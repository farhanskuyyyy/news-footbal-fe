<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Berita')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <header class="border-b bg-white shadow-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('news.index') }}" class="text-xl font-bold tracking-tight text-gray-900">📰 News Portal</a>
                <nav class="flex items-center gap-4 text-sm font-medium">
                    <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">Berita</a>
                    <a href="{{ route('upload.create') }}" class="{{ request()->routeIs('upload.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">📤 Upload Gambar</a>
                </nav>
            </div>
            <span class="text-sm text-gray-500">RnD CI/CD</span>
        </div>
    </header>


    <main class="mx-auto max-w-5xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-sm text-gray-500">
            Sumber data: backend Go News API
        </div>
    </footer>
</body>
</html>
