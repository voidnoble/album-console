@extends('index')

@section('title', 'Home')

@section('breadcrumb')
    <li><a href="/">Home</a></li>
@endsection

@section('contents')
    <style>
        .page-title {
            display: block;
            margin: 1rem 0;
            width: 100%;
            font-size: 3rem;
        }

        .page-subtitle {
            display: block;
            padding: 1rem;
            width: 100%;
            background-color: #ddd;
            font-size: 2rem;
        }

        .article {
            display: -webkit-flex;
            display: flex;
            flex-wrap: wrap;
            -webkit-flex-flow: row wrap;
            flex-flow: row wrap;
            width: 100%;
            margin: 0 auto;
            padding: 0 1rem 1rem 0;
        }

        .component {
            margin-top: 1rem;
            margin-left: 1rem;
            background-color: #fff;
        }

        .component > h3 {
            font-size: 3rem;
        }

        /* flex : flex-grow flex-shrink flex-basis */
        .article .row {
            flex: 1 0 95%;
            padding: 1rem;
        }

        .article .col {
            flex: 1 1 45%;
            padding: 1rem;
        }

        .active-users-container {
            background: hsl(30,10%,95%);
            border: 1px solid #d4d2d0;
            border-radius: 4px;
            font-weight: 300;
            padding: .5em 1.5em;
            white-space: nowrap;
        }

        .ActiveUsers-value {
            font-size: 13rem;
        }
    </style>


    <div id="embed-api-auth-container"></div>

    <div class="article">
        <div class="component col">
            <h3>홈페이지 (뉴스)</h3>
            <div id="active-news-users-container" class="active-users-container">로딩중...</div>
        </div>
        <div class="component col">
            <h3>커뮤니티</h3>
            <div id="active-community-users-container" class="active-users-container">로딩중...</div>
        </div>
    </div>

    <h2 class="page-subtitle">홈페이지</h2>

    <div class="article">
        <div class="component row">
            <h3>금일 컨텐츠 페이지뷰</h3>
            <div id="chart-news-pageviews-per-page-container">로딩중...</div>
        </div>
        <div class="component col">
            <h3>최근 7일간 접속자, 페이지뷰</h3>
            <div id="chart-news-users-and-pageviews-container">로딩중...</div>
        </div>
        <div class="component col">
            <h3>최근 1일간 소셜 유입</h3>
            <div id="chart-news-users-from-social-container">로딩중...</div>
        </div>
        <div class="component col">
            <h3>최근 1일간 포털 유입</h3>
            <div id="chart-news-users-from-portal-container">로딩중...</div>
        </div>
    </div>

    <h2 class="page-subtitle">커뮤니티</h2>

    <div class="article">
        <div class="component row">
            <h3>최근 1일간 컨텐츠 페이지뷰</h3>
            <div id="chart-community-pageviews-per-page-container">로딩중...</div>
        </div>
        <div class="component col">
            <h3>최근 7일간 접속자, 페이지뷰</h3>
            <div id="chart-community-users-and-pageviews-container">로딩중...</div>
        </div>
    </div>
@endsection

@section('inlineScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    {{--<script src="https://apis.google.com/js/client.js?onload=init"></script>--}}
    <script src="https://apis.google.com/js/api.js"></script>
    <script>
        function init() {
            gapi.client.setApiKey('AIzaSyBQ3_4u-ykcbYGxLr-WVUWkodvdxuIoBjI');
            gapi.client.load('analytics', 'v4').then(makeRequest);
        }

        function makeRequest() {
            console.log('Google API makeRequest();');
            /*var request = gapi.client.analytics.url.get({
             'shortUrl': 'http://goo.gl/fbsS'
             });
             request.then(function(response) {
             appendResults(response.result.longUrl);
             }, function(reason) {
             console.log('Error: ' + reason.result.error.message);
             });*/
        }

        // Load Google Embed API library
        (function(w,d,s,g,js,fs){
            g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
            js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
            js.src='https://apis.google.com/js/platform.js';
            fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
        }(window,document,'script'));

        gapi.analytics.ready(function() {
            var sourceLabel = {
                'Source': '소스',
                'Users': '방문자수',
                'Pageviews': '페이지뷰',
                'Social Network': '소셜 네트워크',
                'Page Title': '페이지 제목',
                'Page': '페이지 URL',
                'Naver': '네이버',
                'm.entertain.naver.com' : '네이버 모바일 연예',
                'm.entertain.nave…' : '네이버 모바일 연예',
                'm.sports.naver.com' : '네이버 모바일 스포츠',
                'm.news.naver.com' : '네이버 모바일 뉴스',
                'entertain.news.naver.com' : '네이버 연예',
                'sports.news.naver.com' : '네이버 스포츠',
                'entertain.naver.com' : '네이버 연예',
                'sports.naver.com' : '네이버 스포츠',
                'news.naver.com' : '네이버 뉴스',
                'newsstand.naver.com' : '네이버 뉴스스탠드',
                'm.media.daum.net' : '다음 모바일 뉴스',
                'm.sports.media.daum.net' : '다음 모바일 스포츠',
                'media.daum.net' : '다음 뉴스',
                'sports.media.daum.net' : '다음 스포츠',
                'm.news.nate.com' : '네이트 모바일 뉴스',
                'news.nate.com' : '네이트 뉴스',
                'm.zum.com' : 'ZUM 모바일',
                'zum.com' : 'ZUM',
                'm.facebook.com' : '페이스북 모바일',
                'facebook.com' : '페이스북',
                'mobile.twitter.com' : '트위터 모바일',
                'twitter.com' : '트위터'
            };

            /**
             * Authorize the user with an access token obtained server side.
             */
            gapi.analytics.auth.authorize({
                container: 'embed-api-auth-container',
                clientid: '824762868865-ajnst0b3o2napapv5lg39u9bplrf7e54.apps.googleusercontent.com'
                /*'serverAuth': {
                 'access_token': '{{ $googleAccessToken or '' }}'
                 }*/
            });

            /**
             * 실시간 활성 유저 컴포넌트
             * https://github.com/googleanalytics/ga-dev-tools/blob/master/src/javascript/embed-api/components/active-users.js
             */
            gapi.analytics.createComponent('ActiveUsers', {
                initialize: function() {
                    this.activeUsers = 0;
                    gapi.analytics.auth.once('signOut', this.handleSignOut_.bind(this));
                },

                execute: function() {
                    // Stop any polling currently going on.
                    if (this.polling_) {
                        this.stop();
                    }

                    this.render_();

                    // Wait until the user is authorized.
                    if (gapi.analytics.auth.isAuthorized()) {
                        this.pollActiveUsers_();
                    }
                    else {
                        gapi.analytics.auth.once('signIn', this.pollActiveUsers_.bind(this));
                    }
                },

                stop: function() {
                    clearTimeout(this.timeout_);
                    this.polling_ = false;
                    this.emit('stop', {activeUsers: this.activeUsers});
                },

                render_: function() {
                    let opts = this.get();

                    // Render the component inside the container.
                    this.container = typeof opts.container == 'string' ?
                            document.getElementById(opts.container) : opts.container;

                    this.container.innerHTML = opts.template || this.template;
                    this.container.querySelector('b').innerHTML = this.activeUsers;
                },

                pollActiveUsers_: function() {
                    let options = this.get();
                    let pollingInterval = (options.pollingInterval || 5) * 1000;

                    if (isNaN(pollingInterval) || pollingInterval < 5000) {
                        throw new Error('Frequency must be 5 seconds or more.');
                    }

                    this.polling_ = true;
                    gapi.client.analytics.data.realtime
                            .get({ids: options.ids, metrics: 'rt:activeUsers'})
                            .then(function(response) {

                                let result = response.result;
                                let newValue = result.totalResults ? +result.rows[0][0] : 0;
                                let oldValue = this.activeUsers;

                                this.emit('success', {activeUsers: this.activeUsers});

                                if (newValue != oldValue) {
                                    this.activeUsers = newValue;
                                    this.onChange_(newValue - oldValue);
                                }

                                if (this.polling_ == true) {
                                    this.timeout_ = setTimeout(this.pollActiveUsers_.bind(this),
                                            pollingInterval);
                                }
                            }.bind(this));
                },

                onChange_: function(delta) {
                    let valueContainer = this.container.querySelector('b');
                    if (valueContainer) valueContainer.innerHTML = this.activeUsers;

                    this.emit('change', {activeUsers: this.activeUsers, delta: delta});
                    if (delta > 0) {
                        this.emit('increase', {activeUsers: this.activeUsers, delta: delta});
                    }
                    else {
                        this.emit('decrease', {activeUsers: this.activeUsers, delta: delta});
                    }
                },

                handleSignOut_: function() {
                    this.stop();
                    gapi.analytics.auth.once('signIn', this.handleSignIn_.bind(this));
                },

                handleSignIn_: function() {
                    this.pollActiveUsers_();
                    gapi.analytics.auth.once('signOut', this.handleSignOut_.bind(this));
                },

                template:
                '<div class="ActiveUsers">' +
                '사이트의 활성 사용자 수:<br><b class="ActiveUsers-value"></b> 명' +
                '</div>'
            });

            var query = {};

            /**
             * 뉴스웹 활성사용자 컴포넌트 실행 (인스턴스 생성)
             * Create a new ActiveUsers instance to be rendered inside of an element
             * with the id "active-users-container" and poll for changes every five seconds.
             * https://ga-dev-tools.appspot.com/embed-api/third-party-visualizations/
             */
            var activeUsersMobile = new gapi.analytics.ext.ActiveUsers({
                container: 'active-news-users-container',
                pollingInterval: 5
            });
            query = {
                ids : 'ga:114108246',
                viewId : '',
                accountId : '',
                propertyId : ''
            };
            activeUsersMobile.set(query).execute();

            /**
             * 뉴스웹 활성사용자 컴포넌트에 애니메이션 적용
             * Add CSS animation to visually show the when users come and go.
             */
            activeUsersMobile.once('success', function() {
                var element = this.container.firstChild;
                var timeout;

                this.on('change', function(data) {
                    var element = this.container.firstChild;
                    var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
                    element.className += (' ' + animationClass);

                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        element.className =
                                element.className.replace(/ is-(increasing|decreasing)/g, '');
                    }, 3000);
                });
            });

            /**
             * 커뮤니티 활성사용자 컴포넌트 실행 (인스턴스 생성)
             */
            var activeUsers = new gapi.analytics.ext.ActiveUsers({
                container: 'active-community-users-container',
                pollingInterval: 5
            });
            query = {
                ids : 'ga:114765536',
                viewId : '',
                accountId : '',
                propertyId : ''
            };
            activeUsers.set(query).execute();

            /**
             * 커뮤니티 활성사용자 컴포넌트에 애니메이션 적용
             * Add CSS animation to visually show the when users come and go.
             */
            activeUsers.once('success', function() {
                var element = this.container.firstChild;
                var timeout;

                this.on('change', function(data) {
                    var element = this.container.firstChild;
                    var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
                    element.className += (' ' + animationClass);

                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        element.className =
                                element.className.replace(/ is-(increasing|decreasing)/g, '');
                    }, 3000);
                });
            });


            /**
             * Creates a new DataChart instance showing sessions over the past 7 days.
             */
            var dataChartMobileTotalUsers = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114108246',
                    'start-date': '7daysAgo',
                    'end-date': 'today',
                    'metrics': 'ga:users,ga:pageviews',
                    'dimensions': 'ga:date'
                },
                chart: {
                    'container': 'chart-news-users-and-pageviews-container',
                    'type': 'LINE',
                    'options': {
                        'width': '100%'
                    }
                }
            });

            dataChartMobileTotalUsers.execute();

            /**
             * Creates a new DataChart instance showing top 5 most popular demos/tools
             * amongst returning users only.
             */
            var dataChartMobileNewsPageviews = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114108246',
                    'start-date': 'today', // https://developers.google.com/analytics/devguides/reporting/core/v3/reference#startDate
                    'end-date': 'today',
                    'metrics': 'ga:pageviews',
                    'dimensions': 'ga:pagePath,ga:pageTitle',
                    'sort': '-ga:pageviews',
                    'filters': 'ga:pagePathLevel1!=/;ga:pagePath=~^/news/articleView.html',
                    'max-results': 10
                },
                chart: {
                    'container': 'chart-news-pageviews-per-page-container',
                    'type': 'TABLE',   // LINE, COLUMN, BAR, TABLE
                    'options': {
                        'title': '금일 뉴스웹 컨텐츠 페이지뷰',
                        'width': '100%'
                    }
                }
            });
            dataChartMobileNewsPageviews.on('success', function(response) {
                // response.chart : the Google Chart instance.
                // response.data : the Google Chart data object.
                //console.log(response.chart);

                // ga:source 레이블들 한글로 변경
                var chartInnerHTML = response.chart.Mt.innerHTML;

                for(var source in sourceLabel) {
                    chartInnerHTML = chartInnerHTML.replace(source, sourceLabel[source]);
                }

                response.chart.Mt.innerHTML = chartInnerHTML;
            });
            dataChartMobileNewsPageviews.execute();

            /**
             * Creates a new DataChart instance showing top 5 most popular demos/tools
             * amongst returning users only.
             */
            var dataChartMobileUsersFromPortal = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114108246',
                    'start-date': '1daysAgo',
                    'end-date': 'today',
                    'metrics': 'ga:users,ga:pageviews',
                    'dimensions': 'ga:source',
                    'sort': '-ga:users',
                    //'filters': 'ga:source!=(direct);ga:source=~(naver.com|daum.net|nate.com|zum.com)',
                    'filters': 'ga:source!=(direct);ga:source=~^(m.entertain.naver|m.sports.naver|m.news.naver|m.media.daum|m.sports.media.daum|m.facebook.com|mobile.twitter.com)',
                    'max-results': 20
                },
                chart: {
                    'container': 'chart-news-users-from-portal-container',
                    'type': 'TABLE',   // LINE, COLUMN, BAR, TABLE
                    'options': {
                        'title': '최근 1일간 포털 유입',
                        'width': '100%'
                    }
                }
            });
            dataChartMobileUsersFromPortal.on('success', function(response) {
                // response.chart : the Google Chart instance.
                // response.data : the Google Chart data object.
                //console.log(response.chart);

                // ga:source 레이블들 한글로 변경
                var chartInnerHTML = response.chart.Mt.innerHTML;

                for(var source in sourceLabel) {
                    chartInnerHTML = chartInnerHTML.replace(source, sourceLabel[source]);
                }

                response.chart.Mt.innerHTML = chartInnerHTML;
            });
            dataChartMobileUsersFromPortal.execute();

            /**
             * Creates a new DataChart instance showing top 5 most popular demos/tools
             * amongst returning users only.
             */
            var dataChartMobileUsersFromSocial = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114108246',
                    'start-date': '1daysAgo',
                    'end-date': 'today',
                    'metrics': 'ga:users,ga:pageviews',
                    'dimensions': 'ga:socialNetwork',
                    'sort': '-ga:users',
                    'filters': 'ga:socialNetwork=~(naver|facebook|twitter);ga:socialNetwork!=(not set)',
                    'max-results': 5
                },
                chart: {
                    'container': 'chart-news-users-from-social-container',
                    'type': 'TABLE',   // LINE, COLUMN, BAR, TABLE
                    'options': {
                        'title': '최근 1일간 소셜 유입',
                        'width': '100%'
                    }
                }
            });
            dataChartMobileUsersFromSocial.on('success', function(response) {
                // response.chart : the Google Chart instance.
                // response.data : the Google Chart data object.
                //console.log(response.chart);

                // ga:source 레이블들 한글로 변경
                var chartInnerHTML = response.chart.Mt.innerHTML;

                for(var source in sourceLabel) {
                    chartInnerHTML = chartInnerHTML.replace(source, sourceLabel[source]);
                }

                response.chart.Mt.innerHTML = chartInnerHTML;
            });
            dataChartMobileUsersFromSocial.execute();


            /**
             * Creates a new DataChart instance showing sessions over the past 7 days.
             */
            var dataChartUsersAndPageView = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114765536',
                    'start-date': '7daysAgo',
                    'end-date': 'today',
                    'metrics': 'ga:users,ga:pageviews',
                    'dimensions': 'ga:date'
                },
                chart: {
                    'container': 'chart-community-users-and-pageviews-container',
                    'type': 'LINE',
                    'options': {
                        'width': '100%'
                    }
                }
            });

            dataChartUsersAndPageView.execute();

            /**
             * Creates a new DataChart instance showing top 5 most popular demos/tools
             * amongst returning users only.
             */
            var dataChartPageviewsPerPage = new gapi.analytics.googleCharts.DataChart({
                query: {
                    'ids': 'ga:114765536',
                    'start-date': 'yesterday',  // https://developers.google.com/analytics/devguides/reporting/core/v3/reference#startDate
                    'end-date': 'today',
                    'metrics': 'ga:pageviews',
                    'dimensions': 'ga:pagePath,ga:pageTitle',
                    'sort': '-ga:pageviews',
                    'filters': 'ga:pagePathLevel1!=/;ga:pagePath!=/c.motorgraph.com',
                    'max-results': 10
                },
                chart: {
                    'container': 'chart-community-pageviews-per-page-container',
                    'type': 'TABLE',
                    'options': {
                        'title': '최근 1일간 컨텐츠 페이지뷰',
                        'width': '100%'
                    }
                }
            });
            dataChartPageviewsPerPage.on('success', function(response) {
                // response.chart : the Google Chart instance.
                // response.data : the Google Chart data object.
                //console.log(response.chart);

                // ga:source 레이블들 한글로 변경
                var chartInnerHTML = response.chart.Mt.innerHTML;

                for(var source in sourceLabel) {
                    chartInnerHTML = chartInnerHTML.replace(source, sourceLabel[source]);
                }

                response.chart.Mt.innerHTML = chartInnerHTML;
            });
            dataChartPageviewsPerPage.execute();
        });

        // Google API client 실행
        gapi.load('client', init);
    </script>
@endsection