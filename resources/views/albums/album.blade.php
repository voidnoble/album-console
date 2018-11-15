@extends('index')

@section('title', $album->name .' 화보')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">화보</a></li>
    <li class="active"><a href="/albums/{{ $album->id }}">{{ $album->name }}</a></li>
@endsection

@section('contents')
    <div class="jumbotron">
        <div class="media">
            <div class="media-object pull-left album-banner">
                <a href="{{ $album->url }}/{{ $album->id }}" target="_blank" style="display:block;width:100%;max-width:660px;overflow:hidden">
                    <img src="{{ $album->url }}/data/albums/{{ substr($album->created_at, 0, 4) }}/{{ substr($album->created_at, 5, 2) }}/banner{{ $album->id }}.jpg" alt="{{ $album->name }} 화보 - 모터그래프" border="0" onerror="this.style.display='none'" style="width:100%;max-width:100%">
                </a>
                <p align="center"><button class="btn btn-default btn-copy-banner">배너 클립보드에 복사</button></p>
            </div>
            <div class="media-body">
                <h3 class="media-heading">{{$album->name}}</h3>
                <div class="media">
                    <p>{{$album->description}}<p>
                    <a href="{{ route('add_image', ['id'=>$album->id]) }}" id="btnAddImage"><button type="button" class="btn btn-primary btn-large">사진추가</button></a>
                    <button id="btnSort" type="button" class="btn btn-default btn-large" title="변경한 사진 순서를 적용">순서적용</button>
                    <a href="{{ route('create_album_banner', ['id'=>$album->id]) }}" id="btnCreateBanner"><button type="button" class="btn btn-default btn-large">배너생성</button></a>
                    <a href="{{ route('edit_album_form', ['id'=>$album->id]) }}" id="btnEditAlbum"><button type="button"class="btn btn-default btn-large">화보수정</button></a>
                    <a href="{{ route('delete_album', ['id'=>$album->id]) }}" id="btnDelAlbum" onclick="return confirm('Are yousure?')"><button type="button" class="btn btn-danger btn-large">화보제거</button></a>
                    {{--<a href="/albums/relnews/{{ $album->id }}" class="btn btn-default" title="화보 관련기사 설정">관련기사</a>--}}
                    <div class="help-block">배너생성시 좌측에 생성된 배너가 표시됩니다.</div>

                    <form class="form-inline">
                        @if(count($albums) > 0)
                        <select name="new_album" class="form-control">
                            @foreach($albums as $others)
                                <option value="{{ $others->id }}">{{ $others->name }}</option>
                            @endforeach
                        </select>
                        <button id="btnMoveSelected" data-href="{{ route('move_images') }}" onclick="return confirm('Are you sure?')" class="btn btn-default">선택이동</button>
                        @endif
                        <button id="btnDelSelected" data-href="{{ route('delete_images') }}" onclick="return confirm('Are you sure?')" class="btn btn-danger">선택삭제</button>
                        <div class="help-block">사진 정보 수정은 항목 택1 하여 더블클릭</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {!! Session::get('success') !!}
        </div>
    @endif

    @if(Session::has('warning'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ Session::get('warning') }}
        </div>
    @endif

    <div class="thumbnails row sortable" data-sortable-id="0" aria-dropeffect="move">
        @foreach($album->Photos as $photo)
            <div class="col-thumbnail col-sm-6 col-md-4 col-lg-3" data-item-sortable-id="{{ $album->order }}" draggable="true" aria-grabbed="false">
                <div class="thumbnail" data-item-id="{{ $photo->id }}">
                    <img class="lazy" alt="{{ $photo->name }}" data-src="<?=$album->url?>/data/albums/<?=substr($photo->image, 0, 4)?>/<?=substr($photo->image, 4, 2)?>/<?=substr($photo->image, 6, 2)?>/thumb/<?=$photo['image']?>" alt="<?=$photo['name'] ?>?w=360&h=300" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                    <div class="caption">
                        <p>{{ $photo->name }}</p>
                        <p>{{ date("Y-m-d h:i",strtotime($photo->created_at)) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 선택 컨트롤 -->
    <div class="selected-controls hidden">
        <a href="javascript:void(0)" class="insert-before bg-info text-center">선택 <span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span> 로 이동</a>
        <a href="javascript:void(0)" class="insert-after bg-info pull-right text-center">선택 <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span> 로 이동</a>
    </div>
    <!-- /선택 컨트롤 -->
@endsection

@section('inlineScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5sortable/0.3.0/html.sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5sortable/0.3.0/html.sortable.angular.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/unveil/1.3.0/jquery.unveil.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.5/clipboard.min.js"></script>
    <script>
        /**
         * 페이지 시작시
         */
        $(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-Token' : '{{ csrf_token() }}' }
            });

            /**
             * image lazy loading
             */
            $("img.lazy").unveil();

            /**
             * 썸네일 클릭시 선택 표시
             */
            $('.thumbnails').on('click', '.thumbnail', function () {
                $(this).toggleClass('selected');

                // 이 요소는 선택 상태가 되었으므로 선택 컨트롤 떼어내기
                detachSelectedControls($(this).closest('.col-thumbnail'));
            });

            /**
             * 썸네일 더블클릭시 이미지 수정페이지로 이동. Afterwords, 레이어 팝업으로 리팩토링.
             */
            $('.thumbnails').on('dblclick', '.thumbnail', function () {
                location.href = "/albums/editimage/"+ $(this).data('item-id');
            });

            /**
             * 사진 Drag & Drop 위치 변경
             * https://github.com/voidberg/html5sortable
             */
            $('.sortable').sortable({
                forcePlaceholderSize: true,
                placeholder: '<div class="col-thumbnail col-sm-6 col-md-4 col-lg-3 sortable-placeholder"><div class="thumbnail"><img alt="placeholder" src="http://placehold.it/291x243?text=Placeholder"><div class="caption"><p>드롭하면 여기로 이동</p></div></div></div>'
            });

            /**
             *  배너 클립보드에 복사
             *  https://developers.google.com/web/updates/2015/04/cut-and-copy-commands
             */
            $('.btn-copy-banner').on('click', function () {
                var elm = document.querySelector('.album-banner > a'),
                        fc = elm.firstChild,
                        ec = elm.lastChild,
                        range = document.createRange(),
                        sel;
                range.selectNode(elm);
                sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                //console.log('Copy banner command was ' + msg);    // debug
            });

            /**
             * 순서적용 버튼 클릭시
             */
            $('#btnSort').on('click', function (evt) {
                loading(true);

                var ids = [];
                $('.sortable .thumbnail').each(function() {
                    ids.push( $(this).data('item-id') );
                });

                var data = {
                    'image_ids': ids.join(',')
                };

                $.post('/albums/sortalbumimages/{{ $album->id }}', data, function(json) {
                    if (!json.success) {
                        alert('정상적으로 처리되지 않았습니다. 다시 시도하여 주십시오.');
                    }

                    loading(false);
                }, 'json');
            });

            /**
             * 배너생성 버튼 클릭시
             */
            $('#btnCreateBanner').on('click', function (evt) {
                evt.preventDefault();

                loading(true);

                var url = $(this).attr('href');

                $.getJSON(url, function(json) {
                    if (json.success == false) {
                        alert(json.reason);
                        return false;
                    }

                    var $banner = $('.album-banner > a');
                    var $bannerImage = $banner.find('img');

                    $banner.attr('href', json.href);
                    $bannerImage.attr('src', json.banner).attr('alt', json.title);

                    loading(false);
                });
            });

            /**
             * 선택이동 버튼 클릭시
             */
            $('#btnMoveSelected').on('click', function (evt) {
                loading(true);

                var ids = [];

                $('.thumbnail.selected').each(function () {
                    ids.push($(this).data('item-id'));
                });

                $.ajax({
                    url: $(this).data('href'),
                    type: 'post',
                    data: ids,
                    dataType: 'json'
                }).done(function(json) {
                    if (json.success == false) {
                        alert(json.reason);
                        return false;
                    }

                    $('.thumbnail.selected').remove();

                    loading(false);
                });
            });

            /**
             * 선택삭제 버튼 클릭시
             */
            $('#btnDelSelected').on('click', function (evt) {
                loading(true);

                var ids = [];

                $('.thumbnail.selected').each(function () {
                    ids.push($(this).data('item-id'));
                });

                var data = {
                    'ids': ids
                };

                $.ajax({
                    url: $(this).data('href'),
                    type: 'post',
                    data: data,
                    dataType: 'json'
                }).done(function(json) {
                    if (json.success == false) {
                        alert(json.reason);
                        return false;
                    }

                    $('.thumbnail.selected').remove();

                    loading(false);
                });
            });

            /**
             * @brief 선택 항목들 컨트롤
             * @description
             *  썸네일 컨테이너에 마우스 올렸을 때 동작
             */
            $('.col-thumbnail').hover(
                function () {
                    if ($('.thumbnail.selected').length == 0 || $(this).find('.selected').length > 0) return;

                    attachSelectedControls(this);
                }, function () {
                    detachSelectedControls(this);
                }
            );
        });

        /**
         * 선택 컨트롤을 인자로 전달된 요소에 붙이기
         */
        function attachSelectedControls(element) {
            // 선택 컨트롤 복제본 만들고 화면에 보이도록
            var selectedControls = $('.selected-controls').html();
            // 선택 컨트롤을 마우스 올려진 요소의 하위 요소로 붙임
            $(element).prepend(selectedControls);
        }

        /**
         * 선택 컨트롤을 인자로 전달된 요소에서 떼어내기
         */
        function detachSelectedControls(element) {
            $(element).find('.insert-before').remove();
            $(element).find('.insert-after').remove();
        }

        /**
         * 선택한 항목들을 특정 위치로 이동 컨트롤
         */
        $('.col-thumbnail').on('click', '.insert-before, .insert-after', function (evt) {
            var targetContainer = $(this).closest('.col-thumbnail').get(0);

            if ($(this).hasClass('insert-before')) {
                $('.thumbnail.selected').each(function (i, elem) {
                    $(targetContainer).before($(elem).closest('.col-thumbnail')[0]);
                }).removeClass('selected');
            }

            if ($(this).hasClass('insert-after')) {
                $('.thumbnail.selected').each(function (i, elem) {
                    $(targetContainer).after($(elem).closest('.col-thumbnail')[0]);
                }).removeClass('selected');
            }

            detachSelectedControls(targetContainer);
        });
    </script>
@endsection