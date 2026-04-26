@props([
    'title' => 'PeerPal'
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">

<header class="border-b border-slate-200 bg-white shadow-sm">
    <nav class="mx-auto flex w-full max-w-6xl items-center gap-2 px-4 py-3 sm:px-6 lg:px-8">
        <a
            href="/"
            class="rounded-md px-3 py-2 text-sm font-medium transition hover:bg-slate-100 {{ request()->is('/') ? 'bg-slate-900 text-white hover:bg-slate-900' : 'text-slate-700' }}"
        >
            Home
        </a>
        <a
            href="/survey_results"
            class="rounded-md px-3 py-2 text-sm font-medium transition hover:bg-slate-100 {{ request()->is('survey_results') ? 'bg-slate-900 text-white hover:bg-slate-900' : 'text-slate-700' }}"
        >
            Survey Results
        </a>
    </nav>
</header>

<main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        {{ $slot }}
    </div>
</main>

</body>
</html>