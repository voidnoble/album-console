/**
 * Created by yjkwak on 15. 10. 15..
 */
var albumApp = angular.module("albumApp", ['ngRoute', 'ngResource', 'ngFileUpload']);
var youtubeApp = angular.module("youtubeApp", ['ngRoute', 'ngResource']);

(function (angular, $) {
    'use strict';

    angular.module("albumApp").config(AlbumAppConfig).run(AlbumAppRun);

    AlbumAppConfig.$inject = ['$routeProvider'];

    function AlbumAppConfig($routeProvider) {
        var getView = function (viewName) {
            return '/album.' + viewName + '.html';
        };

        $routeProvider
            // route for the home page. href="#/"
            .when('/', {
                templateUrl: 'home.index.html'
            })
            // route for the list page
            .when('/album', {
                templateUrl: getView('lists'),
                controller: 'FilesystemController',
                controllerAs: 'fsCtrl'
            })
            // route for the list page
            .when('/album/:dir', {
                templateUrl: getView('lists'),
                controller: 'FilesystemController',
                controllerAs: 'fsCtrl'
            })
            .otherwise({
                redirectTo: '/'
            });
    }

    AlbumAppRun.$inject = ['$rootScope', '$routeParams', '$http'];

    function AlbumAppRun($rootScope, $routeParams, $http) {
        $rootScope.host = (/^localhost/.test(location.host) || /^console\.motorgraph\.local/.test(location.host))? location.host : "console.motorgraph.com";
        $rootScope.endpoint = location.protocol +"//"+ $rootScope.host +"/api/album/v1/dirs";
        $rootScope.datas = {};
        $rootScope.album = {
            title: '',
            description: ''
        };

        $rootScope.$on('$routeChangeSuccess', function(e, current, pre) {
            var ol = [
                {
                    path: '/',
                    name: 'Home'
                }
            ];
            if (location.hash.match(/^#\/album/)) {
                ol.push({
                    path: '#/album',
                    name: 'Album'
                });
            }

            var dir = $routeParams.dir;
            var dirs = (typeof dir == 'undefined')? "" : dir.split('.');

            var breadcrumb = "";
            for(var i = 0; i < dirs.length; i++) {
                var li = {
                    path: '#/album/'+ breadcrumb + dirs[i],
                    name: dirs[i]
                };
                ol.push(li);
                breadcrumb += dirs[i] +".";
            }

            $rootScope.breadcrumbs = ol;
        });

        $rootScope.showMenu = function (id) {
            angular.element(document.querySelector('[data-menu="' + id + '"]').parentNode).toggleClass('is-visible');
        };

        $rootScope.toggleUploadModal = function () {
            $('#uploadModal').modal('toggle');
        };

        $rootScope.createDir = function () {
            $('#dirModal').modal('show');
        };

        $rootScope.openResult = function () {
            var host = ($rootScope.host.match(/local/))? "album.motorgraph.local" : "album.motorgraph.com";
            var dir = ($routeParams.dir).replace(/\./g, '/');
            var url = location.protocol +"//"+ host +"/"+ dir;

            window.open(url);
        };

        $rootScope.rescan = function () {
            var dir = $routeParams.dir;
            var url = $rootScope.endpoint +"/"+ dir +"/rescan";

            $http.get(url).then(
                function (response) {
                    if (response.data.success == false) return false;

                    location.reload();
                },
                function (error) {
                    console.error(error);
                }
            );
        };

        $rootScope.setupAlbum = function () {
            var datas = $('.container .row[ng-view]').controller().datas;

            if (datas.carousel) {
                var dir = $routeParams.dir.replace(/\./g, '/');
                var url = "/data/album/" + dir + "/manifest.json";

                $http.get(url).then(
                    function (response) {
                        if (typeof response.data.title != 'undefined') $rootScope.album.title = response.data.title;
                        if (typeof response.data.description != 'undefined') $rootScope.album.description = response.data.description;
                    }
                );
            }

            $('#albumModal').modal('toggle');
        };

        $rootScope.createBanner = function () {
            if ($rootScope.datas.length < 6) {
                alert("필수 조건 : 이미지 6건");
                return false;
            }

            var dir = $routeParams.dir;
            var url = $rootScope.endpoint +"/"+ dir +"/banner";
            $http.get(url).then(
                function (response) {
                    if (response.data.success == false) {
                        alert(response.data.reason);
                        return false;
                    }

                    $rootScope.banner = {
                        src: response.data.banner,
                        href: response.data.href,
                        alt: response.data.title
                    };

                    $rootScope.banner.src = $rootScope.banner.src.replace(/\/\/banner/, '/banner');

                    $('#bannerModal').modal('show');
                }
            );
        };
    }
})(window.angular, jQuery);
/**
 * Created by yjkwak on 15. 10. 15..
 */
(function(angular) {
    'use strict';

    angular
        .module('albumApp')
        .controller('FilesystemController', FilesystemController);

    FilesystemController.$inject = ['$scope', '$http', '$routeParams', 'FilesystemFactory', 'FilesFactory'];

    /**
     * @description 파일시스템 컨트롤
     * @param $scope
     * @param $http
     * @constructor
     */
    function FilesystemController($scope, $http, $routeParams, FilesystemFactory, FilesFactory) {
        $scope.absoluteDir = "";

        var self = this;

        self.datas = {};
        self.dir = "";
        self.from = "";

        self.url = location.protocol +"//"+ $scope.host;

        self.getDir = function () {
            if (typeof $routeParams.dir != 'undefined') {
                self.dir = $routeParams.dir;
                $scope.absoluteDir = self.dir.replace(/\./gi, '/');
                FilesystemFactory.setRouteParams($routeParams);
            }
        };

        self.editDir = function (index) {
            // 레코드 행 로딩
            var data = self.datas.dirs[index];
            FilesystemFactory.setIndex(index);
            // 모달창 컨트롤러 변수에 값 할당
            var formController = angular.element(dirEditModal).controller();
            formController.from_name = data.realname;
            formController.from_title = data.title;
            // 모달창 보이기
            $('#dirEditModal').modal('show');
        };

        self.delDir = function (index) {
            if (!confirm('주의! 하위 디렉토리까지 전부 삭제됩니다. Are you sure?')) return false;

            var dir = self.datas.dirs[index].dirname;
            if (!dir) dir = ".";

            var url = $scope.endpoint +'/'+ dir.replace(/\//g, '.');

            var data = {
                index: index
            };

            $http({
                method: 'DELETE',
                url: url,
                data: data
            }).then(
                function(response) {    // success
                    if (response.data.success == false) {
                        return false;
                    }

                    self.datas.dirs.splice(response.data.index, 1);
                },
                function (response) {   // error
                    console.info(response);
                }
            );
        };

        self.editFile = function (index) {
            alert('under construction');
            return;

            var file = self.datas.files[index];
            var dir = file.dirname;
            var fileName = file.basename;

            if (!dir) dir = ".";

            var url = self.url;
            url += '/files';

            $http.patch(url, {
                dir: dir,
                file: fileName
            }).then(
                function(response) {    // success
                    //TODO: when response successful then edit current element
                    if (response.data.success) {
                        //
                    }
                },
                function (response) {   // error
                    console.info(response);
                }
            );
        };

        self.delFile = function (index) {
            if (!confirm('Are you sure?')) return false;

            var file = self.datas.files[index];
            var dir = (file.dirname)? file.dirname.replace(/^\//, "").replace(/\//g, ".") : "~";
            var fileName = file.basename;

            FilesFactory.delete({ dir: dir, id: fileName, index: index })
                .$promise.then(function (result) {
                    if (result.success == false) {
                        return false;
                    }

                    self.datas.files.splice(result.index, 1);
                });
        };

        self.init = function () {
            self.getDir();

            FilesystemFactory.rest.get({dir: self.dir})
                .$promise.then(function (datas) {
                    if (typeof datas.success != 'undefined') {
                        var ctrl = angular.element('.container .row').controller();
                        ctrl.init();
                    }

                    self.datas = datas;
                });
        };

        self.init();
    }
})(window.angular);
/**
 * @brief
 * @author Created by yjkwak
 * @date 15. 10. 22.
 * @description
 */
(function(angular) {
    'use strict';

    angular
        .module('albumApp')
        .controller('FormController', FormController);

    FormController.$inject = ['$scope', '$http', 'FilesystemFactory'];

    /**
     * @description 폼 컨트롤
     * @param $scope
     * @param $http
     * @constructor
     */
    function FormController($scope, $http, FilesystemFactory) {
        var self = this;

        self.routeParams = {};
        self.dir = "";

        self.createDirSubmit = function (isValid) {
            self.submitted = true;

            if (!isValid) {
                if (dirForm.dir_name.$error.required) {
                    alert('폴더명을 입력하세요.');
                }
                if (dirForm.dir_title.$error.required) {
                    alert('폴더 제목을 입력하세요.');
                }
                return false;
            }

            self.routeParams = FilesystemFactory.getRouteParams();
            self.dir = self.routeParams.dir;

            var data = {
                pwd: self.dir,
                realname: dirForm.dir_name.value,
                title: dirForm.dir_title.value
            };

            var url = $scope.endpoint +"/create";

            $http({
                method: 'POST',
                url: url,
                data: data
            }).then(
                function (response) {   // success
                    if (response.data.success == false) {
                        return false;
                    }

                    var data = response.data.data;
                    // Reference data list controller
                    var fsController = angular.element('.container > .row').controller();
                    fsController.datas.dirs.push(data);

                    // Close modal dialog
                    $('#dirModal').modal('hide');
                    // Clear form
                    dirForm.dir_title.value = "";
                },
                function (response) {   // error
                    console.log(response);
                }
            );
        };

        self.editDirSubmit = function (isValid) {
            if (!isValid) {
                if (dirEditForm.to.$error.required) {
                    alert('변경할 디렉토리명을 입력하세요.');
                }
                return false;
            }

            self.routeParams = FilesystemFactory.getRouteParams();
            self.dir = self.routeParams.dir;

            var data = {
                index: FilesystemFactory.getIndex(),
                name: dirEditForm.to_name.value,
                title: dirEditForm.to_title.value
            };

            var url = $scope.endpoint +'/'+ (self.dir? self.dir : '') +'.'+ dirEditForm.from_name.value;

            $http({
                method: 'PATCH',
                url: url,
                data: data
            }).then(
                function(response) {    // success
                    if (response.data.success == false) {
                        var msg = response.data.reason || '처리중 오류 발생! 다시 시도하여 주십시오.';
                        alert(msg);
                        return false;
                    }

                    // Reference data list controller
                    var row = angular.element('.container > .row').controller().datas.dirs[response.data.index];
                    // Apply modified value
                    row.realname = dirEditForm.to_name.value;
                    row.title = dirEditForm.to_title.value;
                    // Close dialog
                    $('#dirEditModal').modal('hide');
                    dirEditForm.to_name.value = "";
                    dirEditForm.to_title.value = "";
                    self.from_name = "";
                    self.from_title = "";
                },
                function (response) {   // error
                    console.info(response);
                }
            );
        };

        self.setupAlbumSubmit = function (isValid) {
            if (!isValid) {
                if (albumForm.title.$error.required) {
                    alert('앨범제목을 입력하세요.');
                }
                return false;
            }

            self.routeParams = FilesystemFactory.getRouteParams();
            self.dir = self.routeParams.dir;

            var data = {
                title: albumForm.title.value,
                description: albumForm.description.value
            };

            var url = $scope.endpoint +"/"+ self.dir +"/setup";
            $http({
                method: 'POST',
                url: url,
                data: data
            }).then(
                function (response) {
                    if (response.data.success == false) return false;

                    // Close modal dialog
                    $('#albumModal').modal('hide');

                    $scope.album.title = albumForm.title.value;
                    $scope.album.description = albumForm.description.value;

                    // Clear form
                    albumForm.title.value = "";
                    albumForm.description.value = "";
                },
                function (error) {
                    console.error(error);
                }
            );
        };
    }
})(window.angular);
/**
 * @brief File Upload Controller
 * @author by yjkwak
 * @date 15. 10. 16..
 * @description https://github.com/danialfarid/ng-file-upload#usage
 */
(function (angular) {
    'use strict';

    angular.module('albumApp')
        .controller('UploadController', UploadController);

    UploadController.$inject = ['$scope', 'Upload', '$routeParams', '$timeout', 'FilesystemFactory'];

    function UploadController($scope, Upload, $routeParams, $timeout, FilesystemFactory) {
        var vm = this;
        vm.dir = $routeParams.dir;
        vm.progress = {
            percentage: 0,
            msg: ""
        };

        vm.removeFile = function (index) {
            $scope.files.splice(index, 1);
        };

        // 파일들 모델에 변경이 있을 경우
        $scope.$watch('files', function () {
            //$scope.upload($scope.files); // 파일 큐에 담은 뒤 submit 버튼 클릭시 submit() 통한 전송을 위해 주석처리해둠
        });
        // 파일 모델에 변경이 있을 경우
        $scope.$watch('file', function () {
            if ($scope.file != null) {
                $scope.files = [$scope.file];
            }
        });

        $scope.submit = function() {
            var metas = [], meta = {};
            for (var i = 0; i < $scope.files.length; i++) {
                meta = {
                    title : angular.element(uploadForm.file_title)[i].value,
                    description : angular.element(uploadForm.file_description)[i].value
                };
                metas.push(meta);
            }
            $scope.upload($scope.files, metas);
        };

        $scope.upload = function(files, metas) {
            angular.element('.upload-modal__progress').show();

            if (files && files.length) {
                var locationHash = (location.hash)? location.hash : ".";
                // send them all for HTML5 browsers
                Upload.upload({
                    url: '/api/album/v1/dirs/'+ locationHash,
                    data: {
                        dir: $routeParams.dir,
                        files: files,
                        metas: metas
                    }
                }).then(
                    // file is uploaded successfully
                    function (response) {
                        if (response.data.success == false) {
                            return false;
                        }

                        var data = response.data.files;
                        // Reference data list controller
                        var fsController = angular.element('.container > .row').controller();
                        fsController.datas.files = fsController.datas.files.concat(data);

                        // Clear the file queue
                        $scope.files = [];

                        // Reset the upload progress component
                        angular.element('#uploadModal').modal('hide');
                        vm.progress.percentage = 0;
                        vm.progress.msg = "";
                    },
                    // handle error
                    function (response) {
                        console.error(response);
                    },
                    // progress notify
                    function (evt) {
                        vm.progress.percentage = parseInt(100.0 * evt.loaded / evt.total);
                        //console.log('progress: ' + parseInt(100.0 * evt.loaded / evt.total) + '% file :'+ evt.config.data.file.name);
                    }
                );
                /*// send them all of each for non HTML5 browsers
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    var title = uploadForm.file_title[i];
                    var description = uploadForm.file_description[i];

                    if (!file.$error) {
                        Upload.upload({
                            url: location.host +'/album/api/v1/dirs/'+ location.hash,
                            method: 'POST',
                            file: file,
                            sendFieldsAs: 'form',
                            fields: {
                                dir: vm.dir,
                                title: title,
                                description: description
                            }
                        }).then(
                            function (data, status, headers, config) {
                                $timeout(function() {
                                    vm.log = 'file: ' + config.data.file.name + ', Response: ' + JSON.stringify(data) + '\n' + vm.log;
                                    console.log(vm.log);
                                });
                            },
                            function (response) {
                                console.log('Error status: '+ response.status);
                            },
                            function (evt) {
                                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                                vm.log = 'progress: '+ progressPercentage + '% '+ evt.conf.data.file.name +'\n'+ vm.log;
                                console.log(vm.log);
                            }
                        );
                    }
                }*/
            } else {
                console.log($scope.files);
            }
        }
    }
})(window.angular);
/**
 * @brief youtube controller
 * @author Created by yjkwak
 * @date 15. 11. 4.
 * @description
 */

(function (angular, $) {
    'use strict';

    angular.module('youtubeApp')
        .controller('YoutubeController', YoutubeController);

    YoutubeController.$inject = ['$scope', '$http'];

    function YoutubeController($scope, $http) {
        var self = this;

        var url = "";

        /*$http.get(url, function (response) {

        });*/
    }
})(window.angular, jQuery);
/**
 * @brief File (include Dir) List Item
 * @author Created by yjkwak
 * @date 15. 10. 24.
 * @description
 *  angular.module('albumApp', ['albumApp.directives.fileItem']) 로 include 후
 *  <FileItem></FileItem> 과 같이 사용
 *  디렉티브 기초 예제는 http://programmingsummaries.tistory.com/248 참고
 */
angular.module('albumApp.directives.fileItem', [])
    .directive('FileItem', FileItem);

function FileItem() {
    return {
        // 선언 방식 E = <FileItem>
        restrict: 'E',
        // attr과 연결 <FileItem item="item" item2="item2" ...>
        scope: {
            item: '='
        },
        replace: true,
        templateUrl: 'album.lists.html',
        link: function (scope, elem, attrs) {
            // <FileItem data-file-model="datas.files">
            // $scope.datas = ['dirs': {}, 'files': {}];
            var dataModel = attrs.fileModel;

            // 모델명을 HTML attribute로 입력했는지 체크
            if (dataModel && angular.isObject(dataModel)) {
                //
            }

            elem.click(function () {
                console.log('albumApp.directives.fileItem directive clicked!');
            });
        }/*,
        controller: function ($scope) {
            console.log($scope.item);
        }*/
    }
}
/**
 * @brief Files REST Factory
 * @author Created by yjkwak
 * @date 15. 10. 20.
 * @description
 */
(function (angular) {
    'use strict';

    angular.module('albumApp')
        .factory("FilesFactory", FilesFactory);

    FilesFactory.$inject = ['$resource'];

    function FilesFactory($resource) {
        return $resource("/api/album/v1/dirs/:dir/files/:id");
    }
})(window.angular);
/**
 * @brief
 * @author Created by yjkwak
 * @date 15. 10. 22.
 * @description
 */
(function(angular) {
    'use strict';

    angular.module('albumApp')
        .factory('FilesystemFactory', FilesystemFactory);

    FilesystemFactory.$inject = ['$resource'];

    function FilesystemFactory($resource) {
        var datas = {
            carousel: false,
            dirs: [],
            files: []
        };

        var rest = $resource("/api/album/v1/dirs/:dir");
        var index = null;
        var routeParams = {};
        var title = "";
        var from = "";
        var to = "";

        return {
            datas: datas,
            rest: rest,
            getRouteParams: getRouteParams,
            setRouteParams: setRouteParams,
            getDatas: getDatas,
            setDatas: setDatas,
            getIndex: getIndex,
            setIndex: setIndex,
            getDir: getDir,
            getFile: getFile,
            addDir: addDir,
            addFile: addFile,
            delDir: delDir,
            delFile: delFile,
            setDir: setDir,
            setFile: setFile
        };

        function getRouteParams() {
            return routeParams;
        }

        function setRouteParams(params) {
            routeParams = params;
        }

        function getDatas() {
            return datas;
        }

        function setDatas(data) {
            datas = data;
        }

        function getIndex() {
            return index;
        }

        function setIndex(idx) {
            index = idx;
        }

        function getDir(index) {
            return datas.dirs[index];
        }

        function getFile(index) {
            return datas.files[index];
        }

        function addDir(items) {
            datas.dirs = datas.dirs.concat(items);
        }

        function addFile(items) {
            datas.files = datas.files.concat(items);
        }

        function delDir(index) {
            datas.dirs.splice(index, 1);
        }

        function delFile(index) {
            datas.files.splice(index, 1);
        }

        function setDir(index, what, value) {
            var row = getDir(index);
            row[what] = value;
        }

        function setFile(index, what, value) {
            var row = getFile(index);
            row[what] = value;
        }
    }
})(window.angular);
//# sourceMappingURL=app.js.map
