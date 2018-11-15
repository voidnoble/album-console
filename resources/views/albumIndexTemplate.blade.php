<!DOCTYPE html>
<!--[if lt IE 8]><html prefix="og: http://ogp.me/ns#" lang="ko" class="no-js ie7 oldie"><![endif]-->
<!--[if IE 8]><html prefix="og: http://ogp.me/ns#" lang="ko" class="no-js ie8 oldie"><![endif]-->
<!--[if IE 9]><html prefix="og: http://ogp.me/ns#" lang="ko" class="no-js ie9"><![endif]-->
<!--[if gt IE 9]><!--> <html prefix="og: http://ogp.me/ns#" lang="ko" class="no-js"> <!--<![endif]-->
<head>
    <title>{{ $title }} - 모터그래프 화보</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="모터그래프,화보,앨범,갤러리,motorgraph,album,photo,gallery">
    <meta property="og:url" content="http://album.motorgraph.com/{{ $dir }}/index.html">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    @if(count($files) > 0)
        @if(env("APP_ENV") == "local")
            <meta property="og:image" content="thumbs/{{ $files[0]['basename'] }}">
            <link rel="image_src" href="thumbs/{{ $files[0]['basename'] }}">
        @else
            <meta property="og:image" content="slides/{{ $files[0]['basename'] }}">
            <link rel="image_src" href="slides/{{ $files[0]['basename'] }}">
        @endif
    @endif
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black translucent">
    <meta name="format-detection" content="telephone=no">
    <link rel="apple-touch-icon-precomposed" href="http://static.motorgraph.com/static/img/logo.svg">
    <link rel="icon" href="http://static.motorgraph.com/static/img/logo.svg" sizes="32x32">
    <!--[if IE]><link rel="shortcut icon" href="http://static.motorgraph.com/static/img/logo.svg"><![endif]-->
    <meta name="msapplication-TileColor" content="#65a244">
    <meta name="msapplication-TileImage" content="http://static.motorgraph.com/static/img/logo.svg">
    <!--<link rel="alternate" href="album.rss" type="application/rss+xml">-->
    <meta name="robots" content="index,follow">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/css/lightgallery.min.css">
    @if(env("APP_ENV") == "local")
        <link rel="stylesheet" href="http://static.motorgraph.local/album/res/common-v3.css">
    @else
        <link rel="stylesheet" href="http://static.motorgraph.com/static/album/res/common-v3.css">
    @endif
    <!--[if lt IE 9]>
    <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <!--[if lt IE 8]><p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> to better experience this site.</p><![endif]-->

    <header class="page-header">
        <hgroup class="container">
            <a href="http://www.motorgraph.com" class="logo" title="모터그래프 홈페이지로 이동">
                <img src="http://static.motorgraph.{{ (env("APP_ENV") == "local")? "local" : "com/static" }}/album/res/logo.png" alt="모터그래프">
            </a>
            <a href="http://album.motorgraph.com" title="모터그래프 화보로 이동">화보</a>
        </hgroup>
    </header>

    <div class="banner__header container">
        <script type="text/javascript" src="http://cas.criteo.com/delivery/ajs.php?zoneid=254978&amp;nodis=1&amp;cb=77016183640&amp;exclude=undefined&amp;charset=UTF-8&amp;loc=http%3A//m.motorgraph.com/"></script>
    </div>

    <article class="container">
        <div class="row">
            <div class="col-md-9">
                <h3 class="page-title">{{ $title }}</h3>
                {{--<!-- Dir Lists -->
                            <ul class="folders" ng-show="datas.dirs.length > 0">
                                <li class="td" ng-repeat="dir in datas.dirs">
                                    <div class="aside">
                                        <a href="./@{{ dir.realname }}" title="@{{ dir.title }}">
                                            <img src="@{{ dir.realname }}/folderthumb.jpg" width="200" height="150" alt="@{{ dir.title }}">
                                        </a>
                                    </div>
                                    <div class="data">
                                        <h4><a href="./@{{ dir.realname }}">@{{ dir.title }}</a></h4>
                                        <div class="caption">@{{ dir.description }}</div>--}}{{--<p class="info">47&nbsp;이미지</p>--}}{{--
                                    </div>
                                </li>
                            </ul>
                            <!-- /Dir Lists -->--}}

                {{--<ul class="thumbs" ng-show="datas.files.length > 0" style="list-style-type: none;">
                    <li class="td" ng-repeat="file in datas.files">
                        <a href="" title="@{{ file.title }}" ng-click="baseCtrl.selectItem($index)">
                            <img src="http://static.motorgraph.com/static/album/res/blank.png" width="200" height="150"
                                 alt="@{{ file.title }}" data-ng-src="thumbs/@{{ file.basename }}" data-ext="@{{ file.extension }}"
                                 data-caption="<h2>@{{ file.title }}</h2>" data-modified="0" data-size="@{{ file.size }} KB"
                                 data-isimage="true" data-width="960" data-height="640">
                        </a>
                    </li>
                </ul>--}}

                @if(count($files) > 0)
                <div class="thumbs row">
                    @foreach($files as $file)
                        <div data-src="slides/{{ $file['basename'] }}" class="item col-xs-6 col-sm-6 col-md-4">
                            <a href="slides/{{ $file['basename'] }}" onclick="return false;" class="thumbnail">
                                <img class="lazy" data-src="thumbs/{{ $file['basename'] }}" alt="{{ $file['title'] }}" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" />
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            <aside class="col-md-3">
                <div class="row">
                    <h4>관련기사</h4>
                    <ul class="list-group">
                    </ul>
                </div>
            </aside>
        </div>
    </article>

    <div class="banner__middle container">
        <div class="row center-block">
            <script type="text/javascript" src="http://cas.criteo.com/delivery/ajs.php?zoneid=140082&amp;nodis=1&amp;cb=40237543102&amp;exclude=undefined&amp;charset=UTF-8&amp;loc=http%3A//m.motorgraph.com/"></script>
        </div>
    </div>

    <footer class="container">
        <p>Copyright &copy; Motorgraph All rights reserved.</p>
    </footer>

    <!-- 광고 -->
    <div class="banner__right-wing">
        <script type="text/javascript">
            document.MAX_ct0 ='';
            var m3_u = (location.protocol=='https:'?'https://cas.criteo.com/delivery/ajs.php?':'http://cas.criteo.com/delivery/ajs.php?');
            var m3_r = Math.floor(Math.random()*99999999999);
            document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
            document.write ("zoneid=140074");document.write("&amp;nodis=1");
            document.write ('&amp;cb=' + m3_r);
            if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
            document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
            document.write ("&amp;loc=" + escape(window.location));
            if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
            if (document.context) document.write ("&context=" + escape(document.context));
            if ((typeof(document.MAX_ct0) != 'undefined') && (document.MAX_ct0.substring(0,4) == 'http')) {
                document.write ("&amp;ct0=" + escape(document.MAX_ct0));
            }
            if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
            document.write ("'></scr"+"ipt>");
        </script>
    </div>
    <div class="banner__left-wing">
        <script type="text/javascript">
            document.MAX_ct0 ='';
            var m3_u = (location.protocol=='https:'?'https://cas.criteo.com/delivery/ajs.php?':'http://cas.criteo.com/delivery/ajs.php?');
            var m3_r = Math.floor(Math.random()*99999999999);
            document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
            document.write ("zoneid=140074");document.write("&amp;nodis=1");
            document.write ('&amp;cb=' + m3_r);
            if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
            document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
            document.write ("&amp;loc=" + escape(window.location));
            if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
            if (document.context) document.write ("&context=" + escape(document.context));
            if ((typeof(document.MAX_ct0) != 'undefined') && (document.MAX_ct0.substring(0,4) == 'http')) {
                document.write ("&amp;ct0=" + escape(document.MAX_ct0));
            }
            if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
            document.write ("'></scr"+"ipt>");
        </script>
    </div>

    <script src="http://static.motorgraph.com/static/album/res/modernizr-2.6.2.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lightgallery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lg-fullscreen.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lg-thumbnail.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lg-video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lg-autoplay.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/1.2.6/js/lg-zoom.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/unveil/1.3.0/jquery.unveil.min.js"></script>
    <script>
        $(document).ready(function(){
            $("img.lazy").unveil(300);
            $('.thumbs').lightGallery({ selector: '.item' });
            // 관련기사 로딩
            $('aside .list-group').load("http://album.motorgraph.{{ (env("APP_ENV") == "local")? "local" : "com" }}/public/data/album/relnews.html");
        });

        // Google Analytics
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-43825484-1', 'auto');
        ga('send', 'pageview');

        var _gaq=_gaq||[];_gaq.push(['_setAccount','UA-43825484-1']);_gaq.push(['_trackPageview']);
        (function(d){var ga=d.createElement('script');ga.async=true;ga.src=('https:'==d.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';
            var s=d.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);
        })(document);
    </script>
</body>
</html>