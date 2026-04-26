@props([
    'title' => 'PeerPal'
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .tab-header {
            display: flex;
            background-color: #f1f1f1;
            border-bottom: 2px solid #ccc;
        }

        .tab-header a {
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            border-right: 1px solid #ccc;
        }

        .tab-header a:hover {
            background-color: #ddd;
        }

        .tab-header a.active {
            background-color: #fff;
            border-bottom: 2px solid #fff;
            font-weight: bold;
        }

        main {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="tab-header">
    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
    <a href="/survey_results" class="{{ request()->is('survey_results') ? 'active' : '' }}">Survey Results</a>
</div>

<main>
    {{ $slot }}
</main>

</body>
</html>