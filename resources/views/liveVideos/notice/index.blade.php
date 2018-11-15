@extends('index')

@section('title', '라이브 방송')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/livevideos">라이브 방송</a></li>
    <li class="active"><a href="/livevideos/notice">공지 관리</a></li>
@endsection

@section('contents')
    <div class="page-header">
        <h1>라이브 방송 공지 관리</h1>
    </div>

    <form name="frmNotice" method="post" action="/livevideos/notice">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="phrase">공지 문구</label>
            <input type="text" name="phrase" id="phrase" value="{{ $phrase }}" class="form-control" placeholder="라이브 영상 공지 문구 입력">
            <div class="help-block">공지 문구 조회 URL: <a href="https://callback.motorgraph.com/livevideos/notice" target="_blank">https://callback.motorgraph.com/livevideos/notice</a></div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">확인</button>
        </div>
    </form>
@endsection

@section('inlineScripts')
@endsection