@extends('index')

@section('title', '화보 관련기사 설정 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums/{{$album_id}}">화보</a></li>
    <li class="active"><a href="/albums/relnews">관련기사 설정</a></li>
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

            <form id="frm1" class="panel panel-default" action="{{ url('albums/relnews') }}" enctype="multipart/form-data">
                <div class="panel-heading">
                    <input type="submit" class="btn btn-default" value="항목추가">
                </div>
                <div class="panel-body">
                    <p>
                        <label for="externUid">기사검색</label>
                        <div class="input-group">
                            <input type="text" name="q" id="q" class="form-control" placeholder="검색어">
                            <span class="input-group-btn">
                                <button id="btnSearch" class="btn btn-default" type="button">검색</button>
                            </span>
                        </div>
                        <!-- 기사 ND소프트측 UID --><input type="hidden" id="externUid" name="externUid" class="form-control">
                    </p>
                    <p>
                        <label for="title">제목</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="기사제목">
                    </p>
                    <p>
                        <label for="link">링크</label>
                        <input type="text" id="link" name="link" class="form-control" placeholder="http://">
                    </p>
                </div>
            </form>
        </div>
        <div class="aside col-md-9">
            <aside class="col-md-3">
                <div class="row">
                    <h4>관련기사</h4>
                    <ul class="list-group sortable">
                        @foreach($data->Articles as $article)
                            <li class="list-group-item"><a href="http://www.motorgraph.com/news/articleView.html?idxno={{ $article->article_id }}" target="_blank">{{ $article->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <div class="template hide">
        <div data-src="{src}" data-item-sortable-id="{id}" draggable="true" aria-grabbed="false" role="option" class="item col-xs-6 col-sm-6 col-md-4">
            <a href="{href}" onclick="return false;" class="thumbnail">
                <img src="{src}" alt="{alt}" border="0">
            </a>
        </div>
    </div>

    <div id="searchResult" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalSearchResult">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{queries.request.searchTerms} : 검색 결과</h4>
                </div>
                <div class="modal-body">
                    결과 전체 수 : {searchInformation.formattedTotalResults}
                    <div class="list-group">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('inlineScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5sortable/0.3.0/html.sortable.min.js"></script>
    <script>
        $(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-Token': '{{ csrf_token() }}' }
            });

            // 스냅샷 데이터가 있으면 선택 로딩
            var snapshot = localStorage.getItem('aside_snapshot');
            if (snapshot) {
                if (confirm('자동저장된 목록 데이터가 있습니다.\n사용하시겠습니까?')) $('aside .list-group').html(snapshot);
            } else {
                // 현재 설정된 관련기사 데이터 로딩
                //$('aside .list-group').load('/data/albums/relnews.html');
            }

            sortable();

            $('aside .list-group').on('click', '.list-group-item', function () {
                $(this).toggleClass('selected');
            });

            // 검색 버튼 클릭시
            $('#btnSearch').on('click', function() {
                var q = $('#q').val();
                if (!q) {
                    alert('검색어를 입력하세요!');
                    $('#q').focus();
                    return false;
                }

                var url = 'https://www.googleapis.com/customsearch/v1?key=AIzaSyBfevRM4PoY_3iLxpl1tOxjiaIZvSTIbEU&cx=009602362331747030406:mehgsqyt-yq&gl=ko&hl=ko-KR&ie=utf8&oe=utf8&start=1&sort=date&q='+ q;
                $.getJSON(url, function (json) {
                    // 검색결과 클리어
                    $('#searchResult .modal-body .list-group').html('');

                    var headerTpl = $('#searchResult .modal-header').html();
                    var bodyTpl = $('#searchResult .modal-body').html();
                    var itemTpl = '<a href="{link}" target="_blank" data-id="{id}" class="list-group-item">';
                    itemTpl += '<h4 class="list-group-item-heading">{title}</h4>';
                    itemTpl += '<p class="list-group-item-text">{htmlSnippet}</p>';
                    itemTpl += '</a>';

                    var items = "", tmpTpl = "";
                    for (var i=0; i < json.items.length; i++) {
                        var item = json.items[i];
                        var id = item.link.replace(/(.+)=(\d+)$/, '$2');
                        // 템플릿을 내부변수에 할당
                        tmpTpl = itemTpl;
                        // 템플릿 변수 치환
                        tmpTpl = tmpTpl.replace('{link}', item.link);
                        tmpTpl = tmpTpl.replace('{id}', id);
                        tmpTpl = tmpTpl.replace('{title}', item.title);
                        tmpTpl = tmpTpl.replace('{htmlSnippet}', item.htmlSnippet);
                        // 결과를 합산
                        items += tmpTpl;
                    }

                    headerTpl = headerTpl.replace('{queries.request.searchTerms}', json.queries.request[0].searchTerms);
                    bodyTpl = bodyTpl.replace('{searchInformation.formattedTotalResults}', json.searchInformation.formattedTotalResults);

                    $('#searchResult .modal-header').html(headerTpl);
                    $('#searchResult .modal-body').html(bodyTpl);
                    $('#searchResult .modal-body .list-group').html(items);

                    $('#searchResult').modal('show');
                });
            });

            // 검색결과 목록중 하나 클릭시 항목 추가 입력 란 채우고 모달 창 닫기
            $('#searchResult .modal-body').on('click', '.list-group-item', function (evt) {
                evt.preventDefault();   // anchor 동작 막기

                $('#externUid').val( $(this).data('id') );
                $('#title').val( $(this).find('.list-group-item-heading').text() );
                $('#link').val( $(this).attr('href') );

                $('#searchResult').modal('hide');
            });
        });

        // https://github.com/voidberg/html5sortable
        function sortable() {
            $('.sortable').sortable({
                forcePlaceholderSize: true,
                placeholderClass: 'list-group-item border',
                dragImage: null
            }).on('sortupdate', function (evt, ui) { // when change sort order
                // saving snapshot
                localStorage.setItem('aside_snapshot', $('aside .list-group').html());
            });
        }

        $('#frm1').on('submit', function (evt) {
            evt.preventDefault();

            $(this).find('input:submit').attr('disabled', true);

            if (!$('#title').val() || !$('#link').val()) {
                if (!$('#title').val()) {
                    alert("제목 누락");
                    $('#title').focus();
                    $(this).find('input:submit').attr('disabled', false);
                    return false;
                }
                if (!$('#link').val()) {
                    alert("링크 누락");
                    $('#link').focus();
                    $(this).find('input:submit').attr('disabled', false);
                    return false;
                }
            }

            // Add item
            $('aside .list-group').append('<li class="list-group-item"><a href="'+ $('#link').val() +'" target="_blank">'+ $('#title').val() +'</a></li>');

            // saving snapshot
            localStorage.setItem('aside_snapshot', $('aside .list-group').html());
            // clear inputs
            $('#q').val('');
            $('#title').val('');
            $('#link').val('');
            // re sortable
            sortable();
            // enabling submit button
            $(this).find('input:submit').attr('disabled', false);

            return false; // ajax 처리이므로 prevent form submit
        });

        $('#btnSave').on('click', function(){
            loading(true);

            var params = [];

            $('aside .list-group a').each(function (i) {
                var data = {
                    'album_id': '{{$album_id}}',
                    'url': $(this).attr('href'),
                    'article_id': $(this).attr('href').replace('http://www.motorgraph.com/news/articleView.html?idxno=', ''),
                    'title': $(this).text(),
                    'order': i
                };

                params.push(data);
            });

            // 저장 처리
            $.ajax({
                url: '/albums/relnews',
                type: 'post',
                data: {
                    'album_id': '{{$album_id}}',
                    'datas': params
                },
                dataType: 'json',
                cache: false
            }).done(function (json) {
                if (json.success == false) {
                    alert('저장중 오류 발생! 다시 시도하여 주십시오.');
                    loading(false);
                    return false;
                }

                // clear
                localStorage.removeItem('aside_snapshot');
                //$('aside .list-group').html('');

                // 현재 설정된 관련기사 데이터 로딩
                //$('aside .list-group').load('/data/albums/relnews.html');

                alert('성공적으로 저장하였습니다!');

                loading(false);
            });
        });

        $('#btnDel').on('click', function () {
            $('aside .list-group-item.selected').remove();
            localStorage.setItem('aside_snapshot', $('aside .list-group').html());
        });
    </script>
@endsection