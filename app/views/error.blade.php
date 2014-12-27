<!DOCTYPE html>
<html>
<head>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>@yield('title')</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
{{ HTML::style('css/default.css') }}
</head>
<body>
<h1><a href="{{ URL::route('home') }}">Магазин</a></h1></h1>
<h2><span>@yield('h1')</span></h2>
@yield('content')
<p><a href="{{ URL::route('home') }}">Главная страница</a></p>
</body>
</html>