@extends('index')

@section('title', '이미지 추가 - 화보들 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">화보들</a></li>
    <li class="active"><a href="/albums/addimage">이미지 추가</a></li>
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
            <form name="addimagetoalbum" method="post" action="{{ route('add_image_to_album') }}" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input type="hidden" name="album_id" value="{{ $album->id }}" />

                <fieldset>
                    <legend>{{ $album->name }} 화보에 이미지 추가</legend>
                    <div class="form-group">
                        <label for="description">이미지명</label>
                        <input name="name" type="text" class="form-control" placeholder="이미지명">
                        <span class="help-block">이미지명 공백 = 파일명을 대신 사용.</span>
                    </div>
                    <div class="form-group">
                        <label for="description">이미지 설명</label>
                        <textarea name="description" class="form-control" placeholder="이미지 설명"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">이미지 선택</label>
                        <input type="file" name="images[]" id="image" multiple="multiple">
                        <span class="help-block">이미지 다중 업로드 지원. 파일선택창에서 여러 파일 선택 가능.</span>
                    </div>
                    <div class="form-group">
                        <label for="name">해시태그</label>
                        <div class="help-block">해시태그 기능 통해 &quot;#기아 #K5 #화보 #화보 #갤러리&quot; 지원(?)</div>
                    </div>
                    <button type="submit" class="btn btn-primary">추가</button>
                    <a href="{{route('show_album')}}/{{$album->id}}"><button type="submit" class="btn btn-default">취소</button></a>
                </fieldset>
            </form>
        </div>
    </div> <!-- /container -->
@endsection

@section('inlineScripts')
    <script>
    </script>
@endsection