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