@extends('index')

@section('pageTitle', 'Youtube')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/youtube">Youtube</a></li>
    <li class="active"><a href="/youtube/playlistitems/{{ $id }}">{{ $playlist->snippet->title }} 재생목록</a></li>
@endsection

@section('contents')
    {{--{{ dd($playlist) }}--}}
    {{--{#201 ▼
    +"kind": "youtube#playlist"
    +"etag": ""0KG1mRN7bm3nResDPKHQZpg5-do/5PoPP0SRcVUUtNytAA6skuhA6B4""
    +"id": "PLEt63ED1pHgTUgJeolx5HexS-l-cSkIae"
    +"snippet": {#205 ▼
    +"publishedAt": "2015-10-28T09:48:35.000Z"
    +"channelId": "UCp0B9n0YYC8E8bJmS5i4oqw"
    +"title": "2015 도쿄모터쇼"
    +"description": ""
    +"thumbnails": {#206 ▼
    +"default": {#207 ▼
    +"url": "https://i.ytimg.com/vi/78piaWi5yy0/default.jpg"
    +"width": 120
    +"height": 90
    }
    +"medium": {#208 ▶}
    +"high": {#209 ▶}
    +"standard": {#210 ▶}
    +"maxres": {#211 ▶}
    }
    +"channelTitle": "Motorgraph 모터그래프"
    +"localized": {#212 ▶}
    }
    +"status": {#213 ▼
    +"privacyStatus": "public"
    }
    }--}}
    {{--{{ dd($playlistItems) }}--}}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">&quot;{{ $playlist->snippet->title }}&quot; 재생목록 영상들</h3>
            <p class="text-info"><small>최근 50건</small></p>
        </div>
        <div class="panel-body">
            @foreach($playlistItems as $item)
            <div class="media">
                @if(isset($item->snippet->thumbnails))
                <div class="media-left media-top">
                    <a href="https://youtu.be/{{ $item->contentDetails->videoId }}" target="_blank">
                        <img class="media-object" src="{{ $item->snippet->thumbnails->default->url }}" width="{{ $item->snippet->thumbnails->default->width }}" height="{{ $item->snippet->thumbnails->default->height }}" alt="영상 기본 썸네일">
                    </a>
                </div>
                @endif
                <div class="media-body">
                    <h4 class="media-heading">{{ $item->snippet->title }}</h4>
                    {{ $item->snippet->description }}
                    <ul style="margin-top: 1rem;">
                        <li>발행일자: {{ $item->snippet->publishedAt }}</li>
                        <li>순서: {{ $item->snippet->position + 1 }}</li>
                        <li>공개여부: {{ ($item->status->privacyStatus == "public")? "공개" : "비공개" }}</li>
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection