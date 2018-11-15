@extends('index')

@section('title', '라이브 방송')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/livevideos">라이브 방송</a></li>
    <li class="active">입력</li>
@endsection

@section('contents')
    <div class="page-header">
        <h1>라이브 방송 입력</h1>
    </div>

    <form method="post" action="/livevideos" role="form" data-toggle="validator">
        {!! csrf_field() !!}
        @if ($id != '')
            {!! method_field('PUT') !!}
        @endif

        <div class="form-group">
            <label for="title" class="control-label">제목 *</label>
            <input type="text" class="form-control" name="title" id="title" placeholder="제목" value="{{ old('title') }}" required>
            {!! $errors->first('title', '<span class="help-block">:message</span>') !!}
        </div>

        <div class="form-group">
            <textarea name="description" id="description" cols="50" rows="3" class="form-control" placeholder="설명">{{ old('description') }}</textarea>
            {!! $errors->first('description', '<span class="help-block">:message</span>') !!}
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="title" class="control-label">시작시간 *</label>
                <input type="datetime-local" class="form-control" name="scheduledStartTime" id="scheduledStartTime" placeholder="시작시간" value="{{ old('scheduledStartTime') }}" required>
                {!! $errors->first('scheduledStartTime', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group col-md-6">
                <label for="title" class="control-label">종료시간</label>
                <input type="datetime-local" class="form-control" name="scheduledEndTime" id="scheduledEndTime" placeholder="종료시간" value="{{ old('scheduledEndTime') }}">
                {!! $errors->first('scheduledEndTime', '<span class="help-block">:message</span>') !!}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="privacyStatus" class="control-label">공개여부 *</label>
                <select name="privacyStatus" id="privacyStatus" class="form-control" required>
                    <option value="public">공개</option>
                    <option value="unlisted">미등록</option>
                    <option value="private">비공개</option>
                </select>
            </div>

            <div class="form-group col-md-6">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="cdnResolution" class="control-label">화질 *</label>
                <select name="cdnResolution" id="cdnResolution" class="form-control" required>
                    <option value="1080p">1080p</option>
                    <option value="1440p">1440p</option>
                    <option value="720p">720p</option>
                    <option value="480p">480p</option>
                    <option value="360p">360p</option>
                    <option value="240p">240p</option>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label for="cdnFrameRate" class="control-label">프레임 비율 *</label>
                <select name="cdnFrameRate" id="cdnFrameRate" class="form-control" required>
                    <option value="30fps">30fps</option>
                    <option value="60fps">60fps</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">확인</button>
    </form>
@endsection

@section('inlineScripts')
    <script>
        $(function () {
            @if (old('privacyStatus'))
                $('#privacyStatus').val("{{ old('privacyStatus') }}");
            @endif

            @if (old('cdnResolution'))
                $('#cdnResolution').val("{{ old('cdnResolution') }}");
            @endif

            @if (old('cdnFrameRate'))
                $('#cdnFrameRate').val("{{ old('cdnFrameRate') }}");
            @endif
        });
    </script>
@endsection