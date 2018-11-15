<!DOCTYPE html>
<html lang="ko" ng-app="albumApp">
<head>
    <title>@yield('title') - Motorgraph Console</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robot" content="noindex,nofallow">

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="./images/logo.svg">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Motorgraph Console">
    <link rel="apple-touch-icon-precomposed" href="./images/logo.svg">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <!--<meta name="msapplication-TileImage" content="/images/logo.svg">
    <meta name="msapplication-TileColor" content="#3372DF">-->

    <link rel="shortcut icon" href="./images/logo.svg">

    <!-- SEO: If your mobile URL is different from the desktop URL, add a canonical link to the desktop page https://developers.google.com/webmasters/smartphone-sites/feature-phones -->
    <link rel="canonical" href="http://console.motorgraph.com/">

    <meta property="fb:app_id" content="851526674895192" />
    <meta property="fb:admins" content="100009266132624" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/css/all.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <header>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">MG Console</a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <!--<li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>-->
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">의견들 <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="/comments" title="의견들 대시보드">의견들 대시보드</a></li>
                                {{--<li role="separator" class="divider"></li>--}}
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">화보 <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="/albums" title="화보관리 루트로 이동">화보관리</a></li>
                                {{--<li role="separator" class="divider"></li>
                                <li><a href="/#album" title="화보관리 루트로 이동">화보 파일관리</a></li>
                                <li><a ng-click="toggleUploadModal()" data-toggle="modal" title="사진들 업로드">업로드</a></li>
                                <li><a ng-click="rescan()" title="재스캔">재스캔</a></li>
                                <li><a ng-click="createDir()" data-toggle="modal" title="디렉토리 생성">디렉토리 생성</a></li>
                                <li><a ng-click="setupAlbum()" title="현 디렉토리 화보 설정">화보설정</a></li>
                                <li><a ng-click="createBanner()" title="현 디렉토리 기반 배너 생성">배너생성</a></li>
                                <li><a ng-click="openResult()" title="현 폴더의 발행된 화보 열기">화보보기</a></li>--}}
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">라이브 방송 <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="/livevideos/" title="라이브 방송 관리">라이브 방송 관리</a></li>
                                <li><a href="/livevideos/comments" title="의견들 대시보드">의견들 대시보드</a></li>
                                <li><a href="/livevideos/captions/backgrounds" title="라이브 영상 자막 배경 관리">자막 배경 관리</a></li>
                                <li><a href="/livevideos/notice" title="라이브 영상 공지 관리">공지 관리</a></li>
                                <li><a href="https://callback.motorgraph.com/node/doc/" target="_blank" title="API 문서">API 문서</a></li>
                                {{--<li role="separator" class="divider"></li>--}}
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Youtube <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="/youtube" title="Youtube 대시보드">유튜브 대시보드</a></li>
                                {{--<li role="separator" class="divider"></li>
                                <li><a ng-click="" title="유튜브 oAuth2 인증">인증</a></li>--}}
                            </ul>
                        </li>
                    </ul>
                    <!--<form class="navbar-form navbar-left" role="search">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Search">
                        </div>
                        <button type="submit" class="btn btn-default">Submit</button>
                    </form>-->
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="/auth/logout" title="Logout">로그아웃</a></li>
                        <li class="dropdown">
                            <a href="helpAlbumImages.html" onclick="return false;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="Help">도움말 <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#help/album">화보</a></li>
                                <li><a href="#help/album/images" onclick="helpAlbumImages();return false;">화보 사진관리</a></li>
                                <li><a href="#help/api">API</a></li>
                                <!--<li><a href="#">Something else here</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">Separated link</a></li>-->
                            </ul>
                        </li>
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container-fluid -->
        </nav>
    </header>

    <nav class="container-fluid">
        <ol class="breadcrumb">
            @yield('breadcrumb')
        </ol>
    </nav>

    <div class="container">
        @yield('contents')
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular-route.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.7/angular-resource.js"></script>
    <script src="/js/all.js"></script>
    <script src="/js/app.js"></script>
    @yield('inlineScripts')
</body>
</html>