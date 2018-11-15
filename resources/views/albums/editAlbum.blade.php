@extends('index')

@section('title', '화보 생성 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/albums">화보</a></li>
    <li class="active"><a href="/albums/createalbum">화보 수정</a></li>
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

            <form name="createnewalbum" method="POST" action="{{ route('edit_album', ['id' => $album->id]) }}" enctype="multipart/form-data">
                {!! csrf_field() !!}

                <fieldset>
                    <legend>화보 생성</legend>

                    <div class="form-group">
                        <label for="name">카테고리</label>
                        <div class="help-block">카테고리 매핑을 통해 &quot;기아 &gt; K5(=화보명)&quot; 지원 기능 구현예정</div>
                    </div>
                    <div class="form-group">
                        <label for="name">화보명</label>
                        <input name="name" type="text" class="form-control" placeholder="화보명" value="{{ $album->name }}">
                    </div>
                    <div class="form-group">
                        <label for="description">화보 설명</label>
                        <textarea name="description" type="text" class="form-control" placeholder="화보 설명">{{ $album->descrption }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="published_at">공개일자</label>
                        <input type="date" name="published_at" id="published_at" value="{{ $album->published_at }}">
                    </div>
                    {{--<div class="form-group">
                        <label for="cover_image">커버 이미지 선택</label>
                        <input type="file" name="cover_image" id="cover_image">
                    </div>--}}
                    <div class="form-group">
                        <label for="name">TAG</label>
                        <input type="text" name="tags" id="tags" value="">
                    </div>
                    <button type="submit" class="btn btn-default">확인</button>
                </fieldset>
            </form>
        </div>
    </div> <!-- /container -->
@endsection

@section('inlineScripts')
    <script>
        // selectize
        var selectize;
        var tags = [
            @foreach ($tags as $tag)
                { tag: "{{ $tag->name }}" },
            @endforeach
        ];

        $(function () {
            // https://github.com/brianreavis/selectize.js/blob/master/docs/usage.md
            var $select = $('#tags').selectize({
                options: tags,
                delimiter: ',',
                persist: false,
                valueField: 'tag',
                labelField: 'tag',
                searchField: 'tag',
                plugins: ['remove_button'],
                create: function(input) {
                    return {
                        tag: input
                    }
                }
            });
            selectize = $select[0].selectize;
            // https://github.com/brianreavis/selectize.js/blob/master/docs/api.md
            @foreach ($tags as $tag)
               selectize.addItem('{{ $tag->name }}');
            @endforeach
        });
    </script>
@endsection