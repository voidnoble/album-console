@extends('index')

@section('title', 'Youtube')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li class="active"><a href="/youtube">Youtube</a></li>
@endsection

@section('contents')
    <div class="col-md-3">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">채널 정보</h3>
                </div>
                <div class="panel-body">
                    {{--{{ dd($channel) }}--}}
                    <dl>
                        <dd class="center-block"><img src="{{ $channel->snippet->thumbnails->default->url }}" alt="Youtube 채널 기본 썸네일"></dd>
                        <dd>조회수: {{ number_format($channel->statistics->viewCount) }}</dd>
                        <dd>영상수: {{ number_format($channel->statistics->videoCount) }}</dd>
                        <dd>의견수: {{ number_format($channel->statistics->commentCount) }}</dd>
                        <dd>구독수: {{ number_format($channel->statistics->subscriberCount) }}</dd>
                        <dd>숨김구독수: {{ ($channel->statistics->hiddenSubscriberCount)? number_format($channel->statistics->hiddenSubscriberCount) : '0' }}</dd>
                        <dd><span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $channel->snippet->publishedAt }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="row list-group">
            <div class="list-group-item disabled panel-heading">
                <h3 class="panel-title">재생목록</h3>
            </div>
            {{--{{ dd($playlists) }}--}}
            @forelse($playlists as $playlist)
                <a class="list-group-item" href="/youtube/playlistitems/{{ $playlist->id }}" data-toggle="tooltip" data-placement="top" title="재생목록으로 이동">
                    <h5 class="media-heading">{{ $playlist->snippet->title }}</h5>
                    <div class="media-left media-top">
                        <img class="media-object"
                             src="{{ $playlist->snippet->thumbnails->default->url }}"
                             width="64"
                             alt="Youtube 재생목록 thumbnail">
                    </div>
                    <div class="media-body">
                        <span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $playlist->snippet->publishedAt }}
                        <br>
                        <span class="glyphicon glyphicon-globe" title="공개여부"></span> {{ ($playlist->status->privacyStatus == "public")? "공개" : "비공개" }}
                    </div>
                </a>
            @empty
                <p>재생목록이 없습니다.</p>
            @endforelse
        </div>
    </div>

    <div class="col-md-9">
        <!-- 최근 의견 -->
        <div class="list-group">
            <div class="list-group-item disabled panel-heading">
                <h3 class="panel-title">
                    최근 의견
                    <small>리스트 세로방향 스크롤</small>
                </h3>
            </div>
            {{--{{ dd($commentThreads) }}--}}
            <div style="height: 30rem; overflow-y: auto;">
            @foreach($commentThreads as $commentThread)
                <div class="list-group-item">
                    <div class="media-left media-top" style="text-align: center;">
                        <a title="{{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}'s Google+ 프로필" href="{{ $commentThread->snippet->topLevelComment->snippet->authorChannelUrl }}" target="_blank">
                            <img src="{{ $commentThread->snippet->topLevelComment->snippet->authorProfileImageUrl }}" border="0">
                        </a>
                        <br>
                        <a title="{{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}'s Youtube 채널" href="{{ $commentThread->snippet->topLevelComment->snippet->authorChannelUrl }}" target="_blank">
                            {{ $commentThread->snippet->topLevelComment->snippet->authorDisplayName }}
                        </a>
                    </div>
                    <div class="media-body">
                        <p>
                            {{ str_replace("<br />", "", $commentThread->snippet->topLevelComment->snippet->textDisplay) }}
                            <span title="답글 수" class="badge">{{ $commentThread->snippet->totalReplyCount }}</span>
                        </p>
                        <p>
                            <span title="등록시간" class="glyphicon glyphicon-time"></span><span title="수정" class="glyphicon glyphicon-check"></span> {{ $commentThread->snippet->topLevelComment->snippet->publishedAt }}
                            <span title="시간" class="glyphicon glyphicon-time"></span><span title="수정" class="glyphicon glyphicon-edit"></span> {{ $commentThread->snippet->topLevelComment->snippet->updatedAt }}
                            <a title="영상 새창에서 보기" href="https://youtu.be/{{ $commentThread->snippet->videoId }}" data-comment-id="{{ $commentThread->id }}" target="_blank">
                                <span class="glyphicon glyphicon-link"></span> 영상
                            </a>
                        </p>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
        <!-- /최근 의견 -->

        <div class="row">
            <div class="col-xs-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">최근등록 5건</h3>
                    </div>
                    <div class="panel-body">
                        {{--{{ dd($latests) }}--}}
                        {{--
                        {
                            +"kind": "youtube#searchResult"
                            +"etag": ""0KG1mRN7bm3nResDPKHQZpg5-do/gPaccJLOiEi9o7zDYVRBiDxmZOU""
                            +"id": {
                              +"kind": "youtube#video"
                              +"videoId": "ryyWhXm-2Pg"
                            }
                            +"snippet": {
                              +"publishedAt": "2015-11-03T02:03:03.000Z"
                              +"channelId": "bla~bla~bla~"
                              +"title": "[2015 도쿄 모터쇼]혼다 시빅 타입R(honda civic type R)"
                              +"description": ""
                              +"thumbnails": {#2135 ▶}
                              +"channelTitle": "motorgraph"
                              +"liveBroadcastContent": "none"
                            }
                        }
                        --}}
                        @foreach($latests as $latest)
                            <div class="media">
                                <div class="media-left media-top">
                                    <a href="https://youtu.be/{{ $latest->id->videoId }}" target="_blank">
                                        <img class="media-object" src="{{ $latest->snippet->thumbnails->default->url }}" alt="Youtube video thumbnail">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">{{ $latest->snippet->title }}</h4>
                                    {{ $latest->snippet->description }}
                                    <small><span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $latest->snippet->publishedAt }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-xs-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">인기영상 톱5</h3>
                    </div>
                    <div class="panel-body">
                        {{--{{ dd($populars) }}--}}
                        @foreach($populars as $popular)
                            <div class="media">
                                <div class="media-left media-top">
                                    <a href="https://youtu.be/{{ $popular->id->videoId }}" target="_blank">
                                        <img class="media-object" src="{{ $popular->snippet->thumbnails->default->url }}" alt="Youtube video thumbnail">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">{{ $popular->snippet->title }}</h4>
                                    {{ $popular->snippet->description }}
                                    <p><small><span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $popular->snippet->publishedAt }}</small></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{--<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">국내 인기영상들</h3>
                </div>
                <div class="panel-body">
                    --}}{{--{{ dd($popularVideos) }}--}}{{--
                    @for ($i = 0; $i < count($popularVideos['ko']); $i++)
                        <ol class="list-group col-xs-6">
                            <li class="list-group-item"><strong>{{ ($i+1) }}위.</strong> {{ $popularVideos['ko'][$i]->snippet->title }}</li>
                            <li class="list-group-item">설명: {{ $popularVideos['ko'][$i]->snippet->description }}</li>
                            <li class="list-group-item"><span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $popularVideos['ko'][$i]->snippet->publishedAt }}</li>
                            <li class="list-group-item">썸네일: <img
                                        src="{{ $popularVideos['ko'][$i]->snippet->thumbnails->default->url }}"
                                        width="{{ $popularVideos['ko'][$i]->snippet->thumbnails->default->width }}"
                                        height="{{ $popularVideos['ko'][$i]->snippet->thumbnails->default->height }}"
                                        alt="Youtube 재생목록 기본 썸네일">
                            </li>
                            <li class="list-group-item">통계:
                                <ul>
                                    <li>조회수: {{ number_format($popularVideos['ko'][$i]->statistics->viewCount) }}</li>
                                    <li>좋아요수: {{ number_format($popularVideos['ko'][$i]->statistics->favoriteCount) }}</li>
                                    <li>의견수: {{ number_format($popularVideos['ko'][$i]->statistics->commentCount) }}</li>
                                </ul>
                            </li>
                            <li class="list-group-item">재생시간: {{ $popularVideos['ko'][$i]->contentDetails->dimension }}</li>
                            <li class="list-group-item">해상도: {{ $popularVideos['ko'][$i]->contentDetails->definition }}</li>
                            <li class="list-group-item">캡션여부: {{ $popularVideos['ko'][$i]->contentDetails->caption }}</li>
                            <li class="list-group-item">상태 :
                                <ul>
                                    <li>업로드 상태: {{ $popularVideos['ko'][$i]->status->uploadStatus }}</li>
                                    <li>공개 여부: {{ $popularVideos['ko'][$i]->status->privacyStatus }}</li>
                                    <li>저작권: {{ $popularVideos['ko'][$i]->status->license }}</li>
                                    <li>Embed 가능 여부: {{ $popularVideos['ko'][$i]->status->embeddable }}</li>
                                    <li>통계공개 여부: {{ $popularVideos['ko'][$i]->status->publicStatsViewable }}</li>
                                </ul>
                            </li>
                        </ol>
                    @endfor
                </div>
            </div>
        --}}

        <!-- 최근활동 5건 -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">최근활동 5건</h3>
            </div>
            <div class="panel-body">
                {{--{{ dd($activities) }}--}}
                @foreach($activities as $activity)
                    <div class="media">
                        <div class="media-left media-top">
                            <a href="#">
                                <img class="media-object"
                                    src="{{ $activity->snippet->thumbnails->default->url }}"
                                    width="{{ $activity->snippet->thumbnails->default->width }}"
                                    height="{{ $activity->snippet->thumbnails->default->height }}"
                                    alt="Youtube 재생목록 기본 썸네일">
                            </a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading" style="line-height: 1.5;">
                                <span class="label label-info" title="활동 종류">
                                    @if ($activity->snippet->type == "upload")
                                        업로드
                                    @elseif ($activity->snippet->type == "playlistItem")
                                        재생목록
                                    @else
                                        {{ $activity->snippet->type }}
                                    @endif
                                </span>
                                &nbsp;{{ $activity->snippet->title }}
                            </h4>
                            {{ $activity->snippet->description }}

                            <p class="text-muted">
                                <span class="glyphicon glyphicon-time" title="발행시간"></span> {{ $activity->snippet->publishedAt }}
                                @if ($activity->snippet->type == "upload")
                                    <a title="영상 새창으로 열기" class="glyphicon glyphicon-link" href="https://youtu.be/{{ $activity->contentDetails->upload->videoId }}" target="_blank"></a> 영상
                                @elseif ($activity->snippet->type == "playlistItem")
                                    <a title="재생목록 새창으로 열기" class="glyphicon glyphicon-link" href="https://youtu.be/{{ $activity->contentDetails->playlistItem->resourceId->videoId }}?list={{ $activity->contentDetails->playlistItem->playlistId }}" target="_blank"></a> 재생목록
                                    <a title="영상 새창으로 열기" class="glyphicon glyphicon-link" href="https://youtu.be/{{ $activity->contentDetails->playlistItem->resourceId->videoId }}" target="_blank"></a> 영상
                                @else
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection