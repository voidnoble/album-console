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