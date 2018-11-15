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