@extends('index')

@section('title', '화보 홈 설정 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">화보</a></li>
    <li class="active"><a href="/albums/home">홈 설정</a></li>
@endsection

@section('contents')
    @if(Session::has('warning'))
        <div class="alert alert-warning alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ Session::get('warning') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <p>
                <input type="button" id="btnSave" class="btn btn-primary" value="저장">
                <input type="button" id="btnDel" class="btn btn-warning" value="선택삭제">
                <div class="help-block">항목 추가하여 배치 후 저장 버튼으로 반영.<br>항목 클릭시마다 선택상태 전환됨.</div>
            </p>

            <form id="frm2" class="panel panel-default" action="{{ action('AlbumsController@home') }}">
                <input type="hidden" name="version" value="2">
                <div class="panel-heading">
                    <input type="submit" class="btn btn-default" value="항목추가"> v2.0 방식
                </div>
                <div class="panel-body">
                    <label for="id">화보번호들 입력</label>
                    <input type="text" name="id[]" id="id" class="form-control">
                    <span class="help-block">다중 입력시 쉼표로 구분. 공백은 제거.<br>화보번호는 화보목록 항목들 우측하단.</span>
                </div>
            </form>

            <form id="frm1" class="panel panel-default" action="{{ action('AlbumsController@home') }}" enctype="multipart/form-data">
                <input type="hidden" name="version" value="1">
                <div class="panel-heading">
                    <input type="submit" class="btn btn-default" value="항목추가"> v1.0 방식
                </div>
                <div class="panel-body">
                    <p><label for="name">앨범명</label> <input type="text" id="name" name="name" class="form-control"></p>
                    <p><label for="link">링크</label> <input type="text" id="link" name="link" class="form-control" placeholder="http://"></p>
                    <p>
                        <label for="cover_image">커버이미지</label> <input type="file" id="cover_image" name="cover_image" class="form-control">
                        <span class="help-block">min-width: 300 (px)</span>
                    </p>
                </div>
            </form>
        </div>
        <div class="thumbnails sortable col-md-9"></div>
    </div>

    <div class="template hide">
        <div data-src="{src}" data-item-sortable-id="{id}" draggable="true" aria-grabbed="false" role="option" class="item col-xs-6 col-sm-6 col-md-4">
            <a href="{href}" class="thumbnail" target="_blank">
                <img src="{src}" alt="{alt}" border="0">
            </a>
        </div>
    </div>

@endsection

@section('inlineScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5sortable/0.3.0/html.sortable.min.js"></script>
    <script>
        var loading_screen;

        $(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-Token': '{{ csrf_token() }}' }
            });

            // 스냅샷 데이터가 있으면 로딩
            var snapshot = localStorage.getItem('thumbnails_snapshot');
            if (snapshot) {
                $('.thumbnails').html(snapshot);
            }

            sortable();

            $('.thumbnails .item').on('click', 'a', function (evt) {
                evt.preventDefault();

                $(this).parent().toggleClass('selected');
            });
        });

        // https://github.com/voidberg/html5sortable
        function sortable() {
            $('.sortable').sortable({
                forcePlaceholderSize: true,
                placeholderClass: 'item border col-md-4',
                dragImage: null
            }).on('sortupdate', function (evt, ui) { // when change sort order
                // saving snapshot
                localStorage.setItem('thumbnails_snapshot', $('.thumbnails').html());
            });
        }

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

        $('#frm2').on('submit', function (evt) {
            loading(true);

            if (!$('#id').val()) {
                alert("앨범번호를 입력해주세요.");
                $('#id').focus();
                loading(false);
                return false;
            }

            var tpl = $('.template').html();
            var data = {
                'ids': $('#id').val()
            };

            $.getJSON('/albums/infos', data, function (json) {
                //console.log(json);
                for(var i = 0; i < json.albums.length; i++) {
                    var album = json.albums[i];

                    var id = album.id;
                    var src = "/data/albums/"+ album.cover_image.substring(0, 4) +"/"+ album.cover_image.substring(4, 6) +"/"+ album.cover_image.substring(6, 8) +"/"+ album.cover_image;
                    var href = "http://albums.motorgraph.com/"+ id;
                    var alt = album.name;

                    var item = tpl.replace(/{id}/g, id);
                    item = tpl.replace(/{src}/g, src);
                    item = item.replace(/{href}/g, href);
                    item = item.replace(/{alt}/g, alt);
                    $('.thumbnails').append(item);
                }

                localStorage.setItem('thumbnails_snapshot', $('.thumbnails').html());

                sortable();

                loading(false);
            });

            return false; // ajax 처리이므로 prevent form submit
        });

        $('#frm1').on('submit', function (evt) {
            loading(true);
            $(this).find('input:submit').attr('disabled', true);

            if (!$('#name').val() || !$('#link').val() || !$('#cover_image').val()) {
                if (!$('#name').val()) {
                    alert("앨범명 누락");
                    $('#name').focus();
                    $(this).find('input:submit').attr('disabled', false);
                    loading(false);
                    return false;
                }
                if (!$('#link').val()) {
                    alert("링크 누락");
                    $('#link').focus();
                    $(this).find('input:submit').attr('disabled', false);
                    loading(false);
                    return false;
                }
                if (!$('#cover_image').val()) {
                    alert("커버이미지 누락");
                    $('#cover_image').focus();
                    $(this).find('input:submit').attr('disabled', false);
                    loading(false);
                    return false;
                }
            }

            var tpl = $('.template').html();

            // HTML5 post with file upload
            var form = $(this);
            var formdata = false;
            if (window.FormData) {
                formdata = new FormData(form[0]);
            }

            var formAction = form.attr('action');

            $.ajax({
                url: '/albums/homeadd',
                type: 'POST',
                data: formdata ? formdata : form.serialize(),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false
            }).done(function(data) {
                if (data.success == false) {
                    alert('전송 중 장애 발생! 다시 시도하여주십시오.');
                    $('#frm1').find('input:submit').attr('disabled', false);
                    return false;
                }
                //console.log(data);
                var album = data.data;
                var src = album.cover_image;
                var href = album.link;
                var alt = album.name;

                var item = tpl.replace(/{id}/g, id);
                item = tpl.replace(/{src}/g, src);
                item = item.replace(/{href}/g, href);
                item = item.replace(/{alt}/g, alt);
                $('.thumbnails').append(item);

                localStorage.setItem('thumbnails_snapshot', $('.thumbnails').html());

                sortable();

                $('#name').val('');
                $('#link').val('');
                $('#cover_image').val('');
                $('#frm1').find('input:submit').attr('disabled', false);

                loading(false);
            });

            return false; // ajax 처리이므로 prevent form submit
        });

        $('#btnSave').on('click', function(){
            loading(true);

            // 썸네일 항목들을 trim 하여 전달
            var htmlSnippet = $('.thumbnails').html().replace(/(^\s*)|(\s*$)/gi, "");

            // 저장 처리
            var data = {
                'htmlSnippet': htmlSnippet
            };
            $.ajax({
                url: '/albums/home',
                type: 'post',
                data: data,
                dataType: 'json',
                cache: false
            }).done(function (json) {
                if (json.success == false) {
                    alert('저장중 오류 발생! 다시 시도하여 주십시오.');
                    loading(false);
                    return false;
                }

                // clear
                localStorage.removeItem('thumbnails_snapshot');
                $('.thumbnails').html('');

                alert('성공적으로 저장하였습니다!');

                loading(false);
            });
        });

        $('#btnDel').on('click', function () {
            $('.thumbnails .item.selected').remove();
            localStorage.setItem('thumbnails_snapshot', $('.thumbnails').html());
        });
    </script>
@endsection