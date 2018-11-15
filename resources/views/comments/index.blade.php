@extends('index')

@section('title', '의견들 - 모터그래프 Console')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li class="active"><a href="/comments">의견들</a></li>
@endsection

@section('contents')
    {{--<!-- 뉴스 사이트 최근 의견 - Facebook comment plugin -->
    <button id="sendFacebookCommentsToSlack">뉴스사이트 최근 의견 Slack에 보내기</button>--}}
    <div class="list-group">
        <div class="list-group-item disabled panel-heading">
            <h3 class="panel-title">뉴스 사이트 최근 의견 (Facebook comment plugin)</h3>
        </div>
        {{--{{ dd($facebook['commentThreads']) }}--}}
        <div class="facebook-comments__list">
            @foreach($facebook['commentThreads'] as $commentThread)
                <div class="list-group-item">
                    <div class="media-body">
                        <h4>
                            <a title="부모글 새창에서 보기" href="{{ $commentThread['link'] }}" target="_blank">
                                {{ $commentThread['title'] }}
                                <small><span class="glyphicon glyphicon-link"></span></small>
                            </a>
                        </h4>
                        <ul class="list-group">
                            @foreach($commentThread['comments'] as $comment)
                            <li class="list-group-item">
                                <p class="clearfix">
                                    <strong data-user-id="{{ $comment['from']['id'] }}">{{ $comment['from']['name'] }}</strong>
                                    <span title="시간" class="glyphicon glyphicon-time"></span><span title="등록" class="glyphicon glyphicon-check"></span> <span data-name="regdate">{{ date("Y-m-d H:i:s", strtotime($comment['created_time'])) }}</span>
                                </p>
                                <p class="list-group-item-text">
                                    {{ $comment['message'] }}
                                </p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- /뉴스 사이트 최근 의견 - Facebook comment plugin -->

    <!-- Youtube 최근 의견 -->
    <div class="list-group">
        <div class="list-group-item disabled panel-heading">
            <h3 class="panel-title">Youtube 최근 의견</h3>
        </div>
        {{--{{ dd($youtube['commentThreads']) }}--}}
        <div>
            @foreach($youtube['commentThreads'] as $commentThread)
                <div class="list-group-item">
                    <span title="답글 수" class="badge">{{ $commentThread->snippet->totalReplyCount }}</span>
                    <div class="media-body">
                        <p class="clearfix">
                            <a class="pull-left media-left" title="{{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}님의 Google+ 프로필 새창에서 보기" href="{{ $commentThread->snippet->topLevelComment->snippet->authorChannelUrl }}" target="_blank">
                                <img src="{{ $commentThread->snippet->topLevelComment->snippet->authorProfileImageUrl }}" border="0">
                            </a>
                            <a style="display: inline-block; margin-bottom: 1rem;" title="{{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}님의 Youtube 채널 새창에서 보기" href="{{ $commentThread->snippet->topLevelComment->snippet->authorChannelUrl }}" target="_blank">
                                {{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}
                            </a>
                            <br>
                            <span title="시간" class="glyphicon glyphicon-time"></span><span title="등록" class="glyphicon glyphicon-check"></span> {{ date("Y-m-d H:i:s", strtotime($commentThread->snippet->topLevelComment->snippet->publishedAt)) }}
                            <span title="시간" class="glyphicon glyphicon-time"></span><span title="수정" class="glyphicon glyphicon-edit"></span> {{ date("Y-m-d H:i:s", strtotime($commentThread->snippet->topLevelComment->snippet->updatedAt)) }}
                            @if (isset($commentThread->snippet->videoId))
                                <a title="영상 새창에서 보기" href="https://youtu.be/{{ $commentThread->snippet->videoId }}" data-comment-id="{{ $commentThread->id }}" target="_blank">
                                    <span class="glyphicon glyphicon-link"></span> 영상
                                </a>
                            @endif
                        </p>
                        <p>
                            {{ str_replace("<br />", "", $commentThread->snippet->topLevelComment->snippet->textDisplay) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- /Youtube 최근 의견 -->
@endsection

@section('inlineScripts')
    <script>
        $('#sendFacebookCommentsToSlack').on('click', function () {
            var currentdate = new Date();
            var datetime = currentdate.getDate() + "/"
                    + (currentdate.getMonth()+1)  + "/"
                    + currentdate.getFullYear() + " "
                    + currentdate.getHours() + ":"
                    + currentdate.getMinutes() + ":"
                    + currentdate.getSeconds();

            var data = "*["+ datetime +"] 자사 뉴스 사이트 최근 의견*\n";

            for(var i = 0; i < $('.facebook-comments__list > .list-group-item').length; i++) {
                var li = $('.facebook-comments__list > .list-group-item')[i];

                var userName = $(li).find('strong').text().trim();
                var regdate = $(li).find('[data-name="regdate"]').text().trim();
                var link = $(li).find('a').attr('href');
                var content = $(li).find('p:last').text().trim();

                data += (i+1) +". "+ userName +" "+ regdate +"\n```"+ content +"```\n> "+ link +"\n";
            }

            var channel = "general";
            channel = "development";
            var url = "https://motorgraph.slack.com/services/hooks/slackbot?token=Vwg8lTaNfoVNjuvcLOv9Gn61&channel=%23"+ channel;

            $.post(url, data, function (response) {
                console.log(response);
            });
        });
    </script>
@endsection