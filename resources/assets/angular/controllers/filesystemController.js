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