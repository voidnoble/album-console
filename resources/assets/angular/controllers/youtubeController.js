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