@extends('index')

@section('title', '화보들 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li class="active"><a href="/albums">화보</a></li>
@endsection

@section('contents')
    <p class="row">
        <a href="{{ route('create_album_form') }}" class="btn btn-primary">화보 생성</a>
        <a href="{{ action('AlbumsController@home') }}" class="btn btn-default" title="화보 홈 설정">홈 설정</a>
    </p>

    @if(Session::has('warning'))
        <div class="alert alert-warning alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ Session::get('warning') }}
        </div>
    @endif

    <div class="row thumbnails">
        @forelse($albums as $album)
            <div class="col-lg-3">
                <a href="{{ route('show_album', array('id'=>$album->id)) }}" class="thumbnail">
                    <img alt="{{ $album->name }}" src="/data/albums/{{ substr($album->cover_image, 0, 4) }}/{{ substr($album->cover_image, 4, 2) }}/{{ substr($album->cover_image, 6, 2) }}/thumb/{{ $album->cover_image }}">
                    <div class="caption">
                        <h4>{{$album->name}}</h4>
                        <p>{{$album->description}}</p>
                        <p>{{count($album->Photos)}} 건</p>
                        <p>{{ date("Y-m-d h:i", strtotime($album->created_at)) }}</p>
                    </div>
                    <button class="album-id btn btn-default" title="앨범번호">{{ $album->id }}</button>
                </a>
            </div>
        @empty
            <p class="well">No data</p>
        @endforelse
    </div>

    <div class="text-center">{!! $albums->render() !!}</div>
@endsection

@section('inlineScripts')
    <script>
    </script>
@endsection