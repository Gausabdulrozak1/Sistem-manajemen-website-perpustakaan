<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f3">
    <title>@yield('title', 'Masuk') · RuangBaca</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-page">
    @yield('content')
</body>
</html>
