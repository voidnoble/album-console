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