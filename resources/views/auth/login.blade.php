<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen grid place-items-center bg-stone-100 p-4 antialiased">
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm bg-white rounded-2xl border border-stone-200 shadow-sm p-6 space-y-4">
        @csrf
        <div class="text-center">
            <div class="mx-auto w-11 h-11 rounded-xl bg-orange-600 text-white grid place-items-center font-bold text-lg">C</div>
            <h1 class="mt-3 text-xl font-bold">Masuk Admin</h1>
            <p class="text-sm text-stone-500">{{ config('app.name') }}</p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>

        <label class="flex items-center gap-2 text-sm text-stone-600">
            <input type="checkbox" name="remember" class="rounded border-stone-300"> Ingat saya
        </label>

        <button class="w-full rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-medium py-2.5 text-sm">Masuk</button>
    </form>
</body>
</html>
