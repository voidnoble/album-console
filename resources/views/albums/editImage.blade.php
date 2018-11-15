@extends('index')

@section('title', '이미지 수정 - 앨범들 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">앨범들</a></li>
    <li class="active"><a href="/albums/addimage">이미지 수정</a></li>
@endsection

@section('contents')
    <div class="container" style="text-align: center;">
        <div class="span4" style="display: inline-block; margin-top:100px;">
            @if($errors->has())
                <div class="alert alert-warning fade in" id="error-block">
                    <?php
                    $messages = $errors->all('<li>:메세지</li>');
                    ?>
                    <button type="button" class="close" data-dismiss="alert">×</button>

                    <h4>주의!</h4>
                    <ul>
                        @foreach($messages as $message)
                            {{ $message }}
                        @endforeach

                    </ul>
                </div>
            @endif
            <form name="addimagetoalbum" method="post" action="{{ route('edit_image') }}" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input type="hidden" name="id" value="{{ $image->id }}">
                <input type="hidden" name="album_id" value="{{ $album->id }}" />

                <fieldset>
                    <legend>{{ $album->name }} 앨범에 이미지 추가</legend>
                    <div class="form-group">
                        <label for="description">이미지명</label>
                        <input name="name" type="text" class="form-control" placeholder="이미지명" value="{{ $image->name }}">
                    </div>
                    <div class="form-group">
                        <label for="description">이미지 설명</label>
                        <textarea name="description" class="form-control" placeholder="이미지 설명">{{ $image->description }}</textarea>
                    </div>
                    {{--<div class="form-group">
                        <label for="image">이미지 선택</label>
                        <input type="file" name="image" id="image">
                    </div>--}}
                    <button type="submit" class="btn btn-primary">확인</button>
                </fieldset>
            </form>
        </div>
    </div> <!-- /container -->
@endsection

@section('inlineScripts')
    <script>
    </script>
@endsection