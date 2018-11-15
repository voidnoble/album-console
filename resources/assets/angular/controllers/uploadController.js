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