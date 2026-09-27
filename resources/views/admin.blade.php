<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Loyal — Admin</title>
    <link rel="icon" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500;600&family=Cairo:wght@400;600&display=swap" rel="stylesheet">
    <script>window.__LOYAL_ADMIN__ = { locales: @json(config('loyal.locales')), appUrl: @json(url('/')) };</script>
    @vite(['resources/css/admin.css', 'resources/js/admin/main.js'])
</head>
<body class="bg-stone-100">
    <div id="admin"></div>
</body>
</html>
