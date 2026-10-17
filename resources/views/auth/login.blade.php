<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Job Monitor</title>

    {{-- ============ FAVICON ============ --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=1">
    {{-- ================================== --}}

    {{-- ============ PWA HEAD ============ --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    {{-- ================================== --}}

    @vite(['resources/css/app.css'])
</head>

<body class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-3">
                <img src="{{ asset('favicon.png') }}?v=1" alt="Job Monitor"
                    class="w-16 h-16 rounded-2xl object-contain shadow-lg shadow-indigo-500/20">
            </div>
            <h1 class="text-xl font-bold text-gray-800">Job Monitor</h1>
            <p class="text-sm text-gray-500 mt-0.5">Masuk untuk melanjutkan</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">

            @if ($errors->any())
                <div
                    class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Username
                    </label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="admin / budi / agus" required autofocus>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-11 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                            placeholder="••••••••" required>

                        {{-- Tombol toggle mata --}}
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-indigo-500 transition"
                            aria-label="Tampilkan password">
                            {{-- Ikon mata tertutup (default = password tersembunyi) --}}
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Ikon mata dicoret (saat password terlihat) --}}
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.293 5.068M6.223 6.223L3 3m3.223 3.223l3.353 3.353m5.091 5.091l3.353 3.353M9.88 9.88a3 3 0 004.24 4.24" />
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember"
                        class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Ingat saya
                </label>

                <button type="submit"
                    class="w-full bg-gradient-to-br from-indigo-500 to-violet-600
                               hover:from-indigo-600 hover:to-violet-700
                               text-white py-2.5 rounded-xl font-semibold text-sm
                               shadow-lg shadow-indigo-500/30 transition-all duration-300">
                    Login
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Laravel 13 · Tailwind v4
        </p>
    </div>

    {{-- ============ TOGGLE PASSWORD ============ --}}
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const eyeOn = document.getElementById('eyeOpen');
            const eyeOff = document.getElementById('eyeClosed');

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            eyeOn.classList.toggle('hidden', isPassword);
            eyeOff.classList.toggle('hidden', !isPassword);
        });
    </script>
    {{-- ========================================= --}}

    {{-- ============ PWA SERVICE WORKER ============ --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>
    {{-- ============================================ --}}

</body>

</html>
