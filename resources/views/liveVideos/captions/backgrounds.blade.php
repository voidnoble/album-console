@extends('index')

@section('title', '라이브 방송 자막 배경 관리 - 모터그래프 콘솔')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
    <li><a href="/livevideos">라이브 방송</a></li>
    <li><a href="/livevideos/captions">자막</a></li>
    <li><a href="/livevideos/captions/backgrounds">배경들</a></li>
@endsection

@section('contents')
    <div class="page-header">
        <h1>라이브 방송 자막 배경들 관리</h1>
    </div>

    <form name="frmLiveVideosCaptionsBackgrounds" class="form-horizontal">
        <div class="form-group">
            <label for="subtitle_background_youtube" class="col-sm-2 control-label">Youtube </label>
            <div class="col-sm-10">
                <input type="file" name="subtitle_background_youtube" id="subtitle_background_youtube" class="form-control">
                <img class="preview__youtube img-thumbnail" src="https://callback.motorgraph.com/images/subtitle_background_youtube.png" alt="라이브 방송 자막 Youtube 배경 이미지">
            </div>
        </div>

        <div class="form-group">
            <label for="subtitle_background_facebook" class="col-sm-2 control-label">Facebook </label>
            <div class="col-sm-10">
                <input type="file" name="subtitle_background_facebook" id="subtitle_background_facebook" class="form-control">
                <img class="preview__facebook img-thumbnail" src="https://callback.motorgraph.com/images/subtitle_background_facebook.png" alt="라이브 방송 자막 Facebook 배경 이미지">
            </div>
        </div>

        <div class="form-group">
            <label for="subtitle_background_afreecatv" class="col-sm-2 control-label">아프리카TV </label>
            <div class="col-sm-10">
                <input type="file" name="subtitle_background_afreecatv" id="subtitle_background_afreecatv" class="form-control">
                <img class="preview__afreecatv img-thumbnail" src="https://callback.motorgraph.com/images/subtitle_background_afreecatv.png" alt="라이브 방송 자막 아프리카TV 배경 이미지">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <input type="submit" value="확인" class="btn btn-primary">
            </div>
        </div>
    </form>
@endsection

@section('inlineScripts')
    <script>
        // 이미지 새로고침
        function refreshImg() {
            var src = '', tmpSrc = [];

            $('.img-thumbnail').each(function () {
                src = $(this).attr('src');
                if (src.indexOf('?') != -1) {
                    tmpSrc = src.split('?');
                    src = tmpSrc[0];
                }

                $(this).attr('src', src +'?t='+ Date.now());
            });
        }

        $('form[name="frmLiveVideosCaptionsBackgrounds"]').on('submit', function () {
            // 검사
            if (!$('#subtitle_background_youtube').val() && !$('#subtitle_background_facebook').val() && !$('#subtitle_background_afreecatv').val()) {
                alert('적어도 하나 이상의 파일을 업로드해주세요.');
                return false;
            }

            // 폼 데이터
            var formData = new FormData(this);

            var endpoint = "https://callback.motorgraph.com/livevideos/captions/backgrounds";

            if (/\.local$/.test(location.host)) endpoint = "http://localhost:3000/livevideos/captions/backgrounds";

            // 처리 요청
            $.ajax({
                url: endpoint,
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false
            }).done(function (json) {
                console.log(json);

                if (!json.success) {
                    alert(json.message);
                    return;
                }

                // 입력 초기화
                $('input:file').val('');

                refreshImg();
            });
        });

        $(function () {
            refreshImg();
        });
    </script>
@endsection