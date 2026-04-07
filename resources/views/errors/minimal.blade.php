<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Error') | Family App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="page-error">
    <main class="error-card">
        <p class="error-code">@yield('code', 'Error')</p>
        <h1 class="error-title">@yield('title', 'Something went wrong')</h1>
        <p class="error-description">@yield('message', 'An unexpected error occurred.')</p>

        <div class="error-actions">
            <a class="error-home-btn" href="{{ url('/') }}">Back to Home</a>
        </div>
    </main>
</body>
</html>
