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