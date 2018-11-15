<!doctype html>
<html lang="ko">
<head>
    <title>Login</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robot" content="noindex,nofallow">

    <link rel="canonical" href="http://console.motorgraph.com/">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap-theme.min.css">

    <style type="text/css">
        body {
            background: url(http://habrastorage.org/files/c9c/191/f22/c9c191f226c643eabcce6debfe76049d.jpg);
        }

        .jumbotron {
            text-align: center;
            width: 30rem;
            height: 50rem;
            border-radius: 0.5rem;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            position: absolute;
            margin: 4rem auto;
            background-color: #fff;
            padding: 2rem;
        }

        .container .glyphicon-list-alt {
            font-size: 10rem;
            margin-top: 3rem;
            color: #f96145;
        }

        .full-width {
            width: 100%;
        }

        input {
            width: 100%;
            margin-bottom: 1.4rem;
            padding: 1rem;
            background-color: #ecf2f4;
            border-radius: 0.2rem;
            border: none;
        }
        h2 {
            margin-bottom: 3rem;
            font-weight: bold;
            color: #ababab;
        }
        .btn {
            border-radius: 0.2rem;
        }
        .btn .glyphicon {
            font-size: 3rem;
            color: #fff;
        }
        .btn.full-width {
            background: #8eb5e2;
            -webkit-border-top-right-radius: 0;
            -webkit-border-bottom-right-radius: 0;
            -moz-border-radius-topright: 0;
            -moz-border-radius-bottomright: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .box {
            position: absolute;
            bottom: 0;
            left: 0;
            margin-bottom: 3rem;
            margin-left: 3rem;
            margin-right: 3rem;
        }
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <form class="jumbotron form-inline" method="post" action="/auth/login">
        {!! csrf_field() !!}

        <div class="container">
            <span class="glyphicon glyphicon-list-alt"></span>
            <h2>MG Console Login</h2>
            <div class="box">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email">
                <input type="password" name="password" id="password" placeholder="password">

                <div class="checkbox-inline">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-default full-width"><span class="glyphicon glyphicon-ok"></span></button>
                <a href="/auth/register" class="btn">Register</a>
            </div>
        </div>
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>