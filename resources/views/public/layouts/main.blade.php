<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix("/css/abovethefold.css", "front") }}">
    <link rel="stylesheet" href="{{ mix("/css/style.css", "front") }}">
    <link rel="stylesheet" href="{{ mix("/css/fonts.css", "front") }}">
    @include('public.layouts.part.favicon')

</head>
<body data-js-controller="{{ $jsControllerName  }}">

<script>
    document.body.className += ' js';
</script>

@include('public.layouts.part.header')

<main>
    @yield('content')
</main>

@include('public.layouts.part.footer')

@if(config('arbory.development.enable_browsersync'))
    <script id="__bs_script__">//<![CDATA[
        document.write( "<script async src='//HOST:3000/browser-sync/browser-sync-client.js?v=2.18.12'><\/script>".replace( "HOST", location.hostname ) );
        //]]></script>
@endif

<script src="{{ mix('/js/app.js', "front") }}"></script>
</body>
</html>