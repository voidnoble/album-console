/**
 * @brief Loading indicator PleaseWait
 * @author Created by yjkwak
 * @date 2016. 3. 2.
 * @description
 *  Usage : loading(true);
 */
var loading_screen;

function loading(bShow) {
    if (bShow) {
        loading_screen = pleaseWait({
            logo: '/images/logo.png',
            backgroundColor: '#2c3e50',
            loadingHtml: '<div class="sk-double-bounce"><div class="sk-child"></div><div class="sk-child sk-double-bounce2"></div></div>'
        });
    } else {
        loading_screen.finish();
    }
}

/**
 * 화보 사진관리 시작 가이드
 */
function helpAlbumImages() {
    if (!(/\/albums\/\d+/).test(location.pathname)) {
        alert('화보 사진관리 페이지\n(화보 중 하나를 클릭하여 사진들 목록이 나타나는 페이지)\n에서 다시 클릭하여 주십시오.');
        return;
    }

    var intro = introJs();
    intro.setOptions({
        steps: [
            {
                intro: "화보 사진 관리 안내입니다."
            }, {
                element: document.querySelector('.jumbotron .media-body .media'),
                intro: "컨트롤 버튼들입니다."
            }, {
                element: document.querySelector('#btnAddImage'),
                intro: "현 화보 사진 추가 페이지로 이동. 사진 다중업로드 가능."
            }, {
                element: document.querySelector('#btnSort'),
                intro: "드래그하여 변경한 사진들 순서를 영구 저장."
            }, {
                element: document.querySelector('#btnCreateBanner'),
                intro: "외부 삽입위한 배너 생성.<br>아래 사진 목록중 첫 6컷을<br>한 이미지에 모아 배열."
            }, {
                element: document.querySelector('#btnEditAlbum'),
                intro: "화보 정보 수정"
            }, {
                element: document.querySelector('#btnDelAlbum'),
                intro: "화보 제거. 주의 - 복구 불가."
            }, {
                element: document.querySelector('#btnMoveSelected'),
                intro: "선택한 사진들을 다른 화보로 이동"
            }, {
                element: document.querySelector('#btnDelSelected'),
                intro: "선택한 사진들을 삭제.<br>주의 - 복구 불가."
            }, {
                element: document.querySelector('.thumbnails .thumbnail:nth-child(1)'),
                intro: "현 화보에 등록된 사진입니다.<ul><li>클릭시 선택상태로 전환됨</li><li>다중 선택 가능</li><li>더블클릭시 수정페이지로 이동함</li><li>드래그하여 순서 변경 후 [순서적용]</li></ul>",
                position: 'top'
            }, {
                element: document.querySelector('.jumbotron .album-banner'),
                intro: "기사에 첨부할 배너 이미지.<br>[배너생성] 버튼으로 생성."
            }, {
                element: document.querySelector('.btn-copy-banner'),
                intro: "클릭하면 기사입력기 본문의<br>이지윅에디터에 붙여넣기 할 수 있도록 클립보드에 복사함."
            }, {
                intro: "화보 사진 관리 안내를 마칩니다."
            }
        ]
    });

    intro.start();
}
