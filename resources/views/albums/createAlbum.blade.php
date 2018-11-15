@extends('index')

@section('title', '화보 생성 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">화보</a></li>
    <li class="active"><a href="/albums/createalbum">화보 생성</a></li>
@endsection

@section('contents')
    <div class="container" style="text-align: center;">
        <div class="span4" style="display: inline-block;margin-top:100px;">

            @if($errors->has())
                <div class="alert alert-warning fade in" id="error-block">
                    <?php
                    $messages = $errors->all('<li>:message</li>');
                    ?>
                    <button type="button" class="close" data-dismiss="alert">×</button>

                    <h4>Warning!</h4>
                    <ul>
                        @foreach($messages as $message)
                            {{ $message }}
                        @endforeach

                    </ul>
                </div>
            @endif

            <form name="createnewalbum" method="POST" action="{{ route('create_album') }}" enctype="multipart/form-data">
                {!! csrf_field() !!}

                <fieldset>
                    <legend>화보 생성</legend>

                    <div class="form-group">
                        <label for="name">카테고리</label>
                        <div class="help-block">카테고리 매핑을 통해 &quot;기아 &gt; K5(=앨범명)&quot; 지원 기능 구현예정</div>
                    </div>
                    <div class="form-group">
                        <label for="name">앨범명</label>
                        <input name="name" type="text" class="form-control" placeholder="앨범명" value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="description">화보 설명</label>
                        <textarea name="description" type="text" class="form-control" placeholder="화보 설명">{{ old('descrption') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="published_at">공개일자</label>
                        <input type="date" name="published_at" id="published_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cover_image">커버 이미지 선택</label>
                        <input type="file" name="cover_image" id="cover_image" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="name">TAG</label>
                        <input type="text" name="tags" id="tags">
                    </div>
                    <button type="submit" class="btn btn-default">생성</button>
                </fieldset>
            </form>
        </div>
    </div> <!-- /container -->
@endsection

@section('inlineScripts')
    <script>
        $(function () {
            $('#tags').selectize({
                delimiter: ',',
                persist: false,
                valueField: 'tag',
                labelField: 'tag',
                searchField: 'tag',
                options: tags,
                create: function(input) {
                    return {
                        tag: input
                    }
                }
            });
        });
    </script>
@endsection