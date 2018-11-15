@extends('index')

@section('title', '라이브 방송 의견들 - 모터그래프 Console')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/livevideos">라이브 방송</a></li>
    <li class="active"><a href="/livevideos/comments">의견들</a></li>
@endsection

@section('contents')
    <!-- Controls -->
    <form class="form-inline row text-center">
        <div class="form-group" style="margin-bottom: 1em">
            <label for="refreshInterval">새로고침 주기</label>
            <select name="refreshInterval" id="refreshInterval" class="form-control">
                <option value="0">정지</option>
                <option value="10" selected>10초</option>
                <option value="30">30초</option>
                <option value="60">1분</option>
                <option value="300">5분</option>
                <option value="600">10분</option>
            </select>
        </div>
    </form>
    <!-- /Controls -->

    <!-- 의견 모듬 -->
    <div class="list-group">
        <div class="list-group-item disabled panel-heading">
            <h3 class="panel-title">
                의견 모듬
                <span class="comments__update-indicator glyphicon glyphicon-refresh hidden" aria-hidden="true"></span>
            </h3>
        </div>
        <div class="comments-list">
        </div>
    </div>
    <!-- /의견 모듬 -->
@endsection

@section('inlineScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.5.3/jquery.timeago.min.js"></script>
    <script>
        var intervalIdComments = null;
        var intervalIdYoutubeLiveChat = null;
        var intervalIdFacebookComment = null;
        var intervalIdAfreecaTVChat = null;

        var tpl = '<div class="list-group-item hidden"> \
            <span title="출처" class="badge ${item.badgeCssClass}">${item.origin}</span> \
            <div class="media-body"> \
                <p class="clearfix"> \
                    <a class="pull-left media-left" href="javascript:void(0);" target="_blank"> \
                        <img src="${item.userImage}" width="50" height="50" border="0"> \
                    </a> \
                    <a style="display: inline-block; margin-bottom: 1rem;" href="javascript:void(0);" target="_blank"> \
                        ${item.userName} \
                    </a> \
                    <br> \
                    <span title="${item.publishedAt}" class="glyphicon glyphicon-time"></span> <span class="time-ago">${item.timeAgo}</span> \
                </p> \
                <p>${item.message}</p> \
            </div> \
        </div>';

        // 의견들 조회
        function fetchComments() {
            var lastPollingAtKey = 'commentsLastPollingAt';
            var $listContainer = $('.comments-list');
            var listLimit = 20;

            // Loading indicator
            $('.comments__update-indicator').removeClass('hidden');

            var url = 'https://callback.motorgraph.com/comments/?force=yes';

            // 로컬 개발 서버인 경우 fetch endpoint 변경
            if (location.host.indexOf('motorgraph.local') > -1) {
                url = 'http://localhost:3000/comments/?force=yes'; // dev
            }

            // 최근 polling 일자
            var lastPollingAt = localStorage.getItem(lastPollingAtKey);
            lastPollingAt = (lastPollingAt == null)? new Date() : new Date(lastPollingAt);

            $.getJSON(url)
                .then(function (json) {
                    if (typeof json == 'undefined') return;

                    if (json.length == 0) {
                        $listContainer.html('<div class="text-center">None</div>');
                        $('.comments__update-indicator').addClass('hidden');
                        return;
                    }

                    // polling 일자 저장
                    localStorage.setItem(lastPollingAtKey, new Date());

                    var item = null,
                        items = [],
                        tmpTpl = '';

                    for (var i = 0, total = json.length; i < total; i++) {
                        item = json[i];

                        item.totalReplyCount = 0;
                        //item.badgeCssClass = 'hidden';

                        // GMT to KST
                        if (item.origin == 'afreecatv') item.publishedAt = parseInt(item.publishedAt);
                        item.publishedAt = new Date(item.publishedAt);

                        // 메세지 게제시간이 기존 polling 시간보다 작거나 같다면 건너뜀
                        if (item.publishedAt <= lastPollingAt) continue;

                        // Times Ago
                        item.timeAgo = $.timeago(item.publishedAt);

                        // YYYY-mm-dd 형식으로
                        item.publishedAt = item.publishedAt.getFullYear() + '-' + ('0'+ (item.publishedAt.getMonth() + 1)).slice(-2) + '-' + ('0'+ item.publishedAt.getDate()).slice(-2) + ' ' + ('0'+ item.publishedAt.getHours()).slice(-2) + ':' + ('0'+ item.publishedAt.getMinutes()).slice(-2) + ':' + ('0'+ item.publishedAt.getSeconds()).slice(-2);

                        item.timeAgo = localeTimeAgo(item.timeAgo);

                        tmpTpl = tpl;

                        Object.keys(item).map(function (key, i) {
                            tmpTpl = tmpTpl.replace('${item.' + key + '}', item[key]);
                        });

                        items.push(tmpTpl);
                    }

                    // 기존 목록의 경과 시간 업데이트
                    var itemPublishedAt = '';
                    $listContainer.find('.list-group-item').each(function (i, listItem) {
                        itemPublishedAt = $(listItem).find('.glyphicon-time').attr('title');
                        $(listItem).find('.time-ago').text(localeTimeAgo($.timeago(itemPublishedAt)));
                    });

                    // 목록의 처음에 추가
                    $listContainer.prepend(items.join('\n'));
                    // 추가한 항목들 서서히 나타나는 효과주기
                    $listContainer.find('.list-group-item.hidden').hide().removeClass('hidden').fadeIn('slow');
                    // 목록 항목 최대 수 제한
                    $('.comments-list > .list-group-item:nth-child('+ listLimit +')').nextAll().remove();

                    // 로딩 끝났으니 로딩중 숨김
                    $('.comments__update-indicator').addClass('hidden');
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    //console.log(textStatus, errorThrown);
                    $listContainer.html('<div class="text-center">'+ textStatus +'</div>');

                    $('.comments__update-indicator').addClass('hidden');
                });
        }

        // Youtube 라이브 채팅 조회
        function fetchYoutubeLiveCaht() {
            var $listContainer = $('.youtube-list');

            // Loading indicator
            $('.youtube__update-indicator').removeClass('hidden');

            var url = 'https://callback.motorgraph.com/youtube/livechat?force=yes';

            // 로컬 개발 서버인 경우 fetch endpoint 변경
            if (location.host.indexOf('motorgraph.local') > -1) {
                url = 'http://localhost:3000/youtube/livechat?force=yes'; // dev
            }

            $.getJSON(url)
                .then(function (json) {
                    if (typeof json == 'undefined') return;

                    if (json.length == 0) {
                        $listContainer.html('<div class="text-center">None</div>');
                        $('.youtube__update-indicator').addClass('hidden');
                        return;
                    }

                    var item = null,
                            items = [],
                            tmpTpl = '';

                    for (var i = 0, total = json.length; i < total; i++) {
                        item = json[i];

                        item.totalReplyCount = 0;
                        //item.badgeCssClass = 'hidden';

                        // GMT to KST and Times Ago
                        item.publishedAt = new Date(item.publishedAt);
                        item.timeAgo = $.timeago(item.publishedAt);
                        item.publishedAt = item.publishedAt.getFullYear() + '-' + item.publishedAt.getMonth() + '-' + item.publishedAt.getDate() + ' ' + item.publishedAt.getHours() + ':' + item.publishedAt.getMinutes() + ':' + item.publishedAt.getSeconds();

                        item.timeAgo = localeTimeAgo(item.timeAgo);

                        tmpTpl = tpl;

                        Object.keys(item).map(function (key, i) {
                            tmpTpl = tmpTpl.replace('${item.' + key + '}', item[key]);
                        });

                        items.push(tmpTpl);
                    }

                    $listContainer.html(items.join('\n'));

                    $('.youtube__update-indicator').addClass('hidden');
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    //console.log(textStatus, errorThrown);
                    $listContainer.html('<div class="text-center">'+ textStatus +'</div>');

                    $('.youtube__update-indicator').addClass('hidden');
                });
        }

        // 페이스북 라이브영상 의견 조회
        function fetchFacebookComments() {
            var $listContainer = $('.facebook-list');

            // Loading indicator
            $('.facebook__update-indicator').removeClass('hidden');

            var url = 'https://callback.motorgraph.com/facebook/comments?force=yes';

            // 로컬 개발 서버인 경우 fetch endpoint 변경
            if (location.host.indexOf('motorgraph.local') > -1) {
                url = 'http://localhost:3000/facebook/comments?status=latest&force=yes'; // dev
            }

            $.getJSON(url)
                .then(function (json) {
                    if (typeof json == 'undefined') return;

                    if (json.length == 0) {
                        $listContainer.html('<div class="text-center">None</div>');
                        $('.facebook__update-indicator').addClass('hidden');
                        return;
                    }

                    var item = null,
                        items = [],
                        tmpTpl = '';

                    for (var i = 0, total = json.length; i < total; i++) {
                        item = json[i];

                        item.totalReplyCount = 0;
                        //item.badgeCssClass = 'hidden';

                        // ISO8601 to KST and Times Ago
                        item.publishedAt = new Date(item.publishedAt);
                        item.timeAgo = $.timeago(item.publishedAt);
                        item.publishedAt = item.publishedAt.getFullYear() + '-' + item.publishedAt.getMonth() + '-' + item.publishedAt.getDate() + ' ' + item.publishedAt.getHours() + ':' + item.publishedAt.getMinutes() + ':' + item.publishedAt.getSeconds();

                        item.timeAgo = localeTimeAgo(item.timeAgo);

                        tmpTpl = tpl;

                        Object.keys(item).map(function (key, i) {
                            tmpTpl = tmpTpl.replace('${item.' + key + '}', item[key]);
                        });

                        items.push(tmpTpl);
                    }

                    $listContainer.html(items.join('\n'));

                    $('.facebook__update-indicator').addClass('hidden');
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    //console.log(textStatus, errorThrown);
                    $listContainer.html('<div class="text-center">'+ textStatus +'</div>');

                    $('.facebook__update-indicator').addClass('hidden');
                });
        }

        // 아프리카TV 채팅 큐 조회
        function fetchAfreecaTVChat() {
            var $listContainer = $('.afreecatv-list');

            // Loading indicator
            $('.afreecatv__update-indicator').removeClass('hidden');

            var url = 'https://callback.motorgraph.com/afreecatv/chat?force=yes'; // production

            // 로컬 개발 서버인 경우 fetch endpoint 변경
            if (location.host.indexOf('motorgraph.local') > -1) {
                url = 'http://localhost:3000/afreecatv/chat?force=yes'; // dev
            }

            $.getJSON(url)
                .then(function (json) {
                    if (typeof json == 'undefined') return;

                    if (json.length == 0) {
                        $listContainer.html('<div class="text-center">None</div>');
                        $('.afreecatv__update-indicator').addClass('hidden');
                        return;
                    }

                    var item = null,
                        items = [],
                        tmpTpl = '';

                    for (var i = 0, total = json.length; i < total; i++) {
                        item = json[i];

                        item.totalReplyCount = 0;
                        //item.badgeCssClass = 'hidden';

                        // timestamp to datetime
                        item.publishedAt = new Date(item.publishedAt);
                        item.timeAgo = $.timeago(item.publishedAt);
                        item.publishedAt = item.publishedAt.getFullYear() + '-' + item.publishedAt.getMonth() + '-' + item.publishedAt.getDate() + ' ' + item.publishedAt.getHours() + ':' + item.publishedAt.getMinutes() + ':' + item.publishedAt.getSeconds();

                        item.timeAgo = localeTimeAgo(item.timeAgo);

                        tmpTpl = tpl;

                        Object.keys(item).map(function (key, i) {
                            tmpTpl = tmpTpl.replace('${item.' + key + '}', item[key]);
                        });

                        items.push(tmpTpl);
                    }

                    $listContainer.html(items.join('\n'));

                    $('.afreecatv__update-indicator').addClass('hidden');
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    //console.log(textStatus, errorThrown);
                    $listContainer.html('<div class="text-center">'+ textStatus +'</div>');

                    $('.afreecatv__update-indicator').addClass('hidden');
                });
        }

        function upateWidgets() {
            fetchYoutubeLiveCaht();
            fetchFacebookComments();
            fetchAfreecaTVChat();
        }

        function intervalUpateWidgets(interval) {
            intervalIdYoutubeLiveChat = setInterval(fetchYoutubeLiveCaht, 1000 * interval);
            intervalIdFacebookComment = setInterval(fetchFacebookComments, 1000 * interval);
            intervalIdAfreecaTVChat = setInterval(fetchAfreecaTVChat, 1000 * interval);
        }

        function intervalClearWidgets() {
            clearInterval(intervalIdYoutubeLiveChat);
            clearInterval(intervalIdFacebookComment);
            clearInterval(intervalIdAfreecaTVChat);
        }

        function localeTimeAgo(str) {
            str = str.replace('less than a minute ago', '1분 내');
            str = str.replace('less than a minute', '1분 내');
            str = str.replace('about', '약');
            str = str.replace('a minute', '1분');
            str = str.replace('an hours', '1시간');
            str = str.replace(' minutes', '분');
            str = str.replace(' minute', '분');
            str = str.replace(' hours', '시간');
            str = str.replace(' days', '일');
            str = str.replace('ago', '전');

            return str;
        }

        function intervalUpateComponent(interval) {
            intervalIdComments = setInterval(fetchComments, 1000 * interval);
        }

        function intervalClearComponent() {
            clearInterval(intervalIdComments);
        }

        // 새로고침 주기 값 변경시
        $('#refreshInterval').change(function () {
            var interval = $(this).val();

            intervalClearComponent();

            if (interval > 0) {
                intervalUpateComponent(interval);
            }
        });

        // 문서 로딩 완료시
        $(function () {
            fetchComments();

            intervalUpateComponent($('#refreshInterval').val());
        });
    </script>
@endsection