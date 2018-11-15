@extends('index')

@section('title', '라이브 방송')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li class="active">라이브 방송</li>
@endsection

@section('contents')
    <div class="page-header">
        <h1>라이브 방송 관리</h1>
    </div>

    @if ($isAuth)
        <button type="button" class="btn btn-primary btn-create">생성</button>
    @else
        <button type="button" class="btn btn-primary btn-auth">구글인증</button>
    @endif

    <h2>목록</h2>
    <h3>Youtube</h3>
    <ul>
        @if ($isAuth)
            {{-- https://api.kdyby.org/class-Google_Service_YouTube_LiveBroadcast.html --}}
            @foreach($broadcasts as $broadcast)
                <li>
                    {{--<pre>{!! print_r($broadcast) !!}</pre>--}}
                    {{ $broadcast['snippet']->getTitle() }}
                    <ul>
                        <li><img src="{{ $broadcast['snippet']['thumbnails']->getDefault()->url }}" alt="" width="{{ $broadcast['snippet']['thumbnails']->getDefault()->width }}" height="{{ $broadcast['snippet']['thumbnails']->getDefault()->height }}"></li>
                        <li><strong>예정 시작시간</strong> {{ $broadcast['snippet']['scheduledStartTime'] }}</li>
                        <li><strong>예정 종료시간</strong> {{ $broadcast['snippet']['scheduledEndTime'] }}</li>
                        <li><strong>실제 시작시간</strong> {{ $broadcast->getSnippet()->getActualStartTime() }}</li>
                        <li><strong>살제 종료시간</strong> {{ $broadcast->getSnippet()->getActualEndTime() }}</li>
                        <li><strong>기본 방송 여부</strong> {{ $broadcast->getSnippet()->getIsDefaultBroadcast() }}</li>
                        <li><strong>라이브 채팅</strong> [{{ $broadcast->getSnippet()->getLiveChatId() }}]</li>
                        <li><strong>생명주기</strong> {{ $broadcast->getStatus()->getLifeCycleStatus() }}</li>
                        <li><strong>공개상태</strong> {{ $broadcast->getStatus()->getPrivacyStatus() }}</li>
                        <li><strong>녹화상태</strong> {{ $broadcast->getStatus()->getRecordingStatus() }}</li>
                        <li>
                            <strong>Stream Id</strong> {{ $broadcast->getContentDetails()->getBoundStreamId() }}
                        </li>
                        <li>
                            <strong>Stream Key</strong> {{ $broadcast['streams']['name'] }}
                        </li>
                        <li>
                            <strong>Stream 기본서버 URL</strong> {{ $broadcast['streams']['url'] }}
                        </li>
                        <li>
                            <strong>Stream 백업서버 URL</strong> {{ $broadcast['streams']['backupUrl'] }}
                        </li>
                        <li>
                            <button type="button" class="btn btn-default youtube__broadcast-delete" data-id="{{ $broadcast['id'] }}">삭제</button>
                        </li>
                    </ul>
                </li>
            @endforeach
        @endif
    </ul>

    <h2>상태</h2>
    @if (!$isAuth)
        <div class="alert alert-info">구글 인증을 받아야 합니다.</div>
    @endif

    @if (session('status'))
        <div class="alert alert-info">
            {!! session('status') !!}
        </div>
    @endif
@endsection

@section('inlineScripts')
    <script>
        $('.btn-create').on('click', function () {
            location.href = '/livevideos/create';
        });

        $('.btn-auth').on('click', function () {
            location.href = '{!! $authUrl !!}';
        });

        $('.youtube__broadcast-delete').on('click', function () {
            var id = $(this).data('id');
            if (!id) return;

            var data = {
                "_method": "DELETE",
                "_token": "{{ csrf_token() }}"
            };

            $.ajax({
                url: '/livevideos/'+ id,
                type: 'DELETE',
                dataType: 'json',
                data: data
            }).then(function (json) {
                //json
            });
        });
    </script>
@endsection