<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ============ FAVICON ============ --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=1">
    {{-- ================================== --}}

    <title>@yield('title', 'Job Monitor') — Job Monitor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ============ PWA HEAD ============ --}}
    @pwaHead
    {{-- ================================== --}}

    {{-- ============ CHART.JS CDN ============ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    {{-- ===================================== --}}

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Toast animation */
        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .toast-enter {
            animation: slideInRight 0.3s ease-out;
        }

        .toast-leave {
            animation: slideOutRight 0.2s ease-in forwards;
        }

        /* Lightbox zoom-in animation */
        @keyframes zoomIn {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    {{-- HEADER --}}
    <x-header title="@yield('title', 'Job Monitor')" />

    {{-- MAIN WRAPPER (tanpa sidebar, full width) --}}
    <main class="pt-[72px] px-4 pb-4 lg:px-6 lg:pb-6">
        @yield('content')
    </main>

    {{-- TOAST CONTAINER --}}
    <div id="toast-container" class="fixed top-20 right-4 z-[200] flex flex-col gap-2 pointer-events-none"
        style="max-width: 380px;">
    </div>

    {{-- CONFIRM MODAL --}}
    <div id="confirm-modal" class="fixed inset-0 z-[210] hidden items-center justify-center p-4" style="display: none;">
        <div id="confirm-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden
                    transform transition-all duration-300 scale-95 opacity-0"
            id="confirm-box">

            <div id="confirm-header" class="px-6 py-5 text-white flex items-start gap-3">
                <div id="confirm-icon-wrap"
                    class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                    <svg id="confirm-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="confirm-title" class="text-lg font-bold">Konfirmasi</h3>
                    <p id="confirm-subtitle" class="text-xs text-white/80 mt-0.5">
                        Perlu tindakan Anda
                    </p>
                </div>
            </div>

            <div class="px-6 py-5">
                <p id="confirm-message" class="text-sm text-gray-600 leading-relaxed">
                    Yakin ingin melanjutkan?
                </p>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" id="confirm-cancel-btn"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700
                               rounded-xl text-sm font-semibold transition">
                    Batal
                </button>
                <button type="button" id="confirm-ok-btn"
                    class="px-4 py-2 text-white rounded-xl text-sm font-semibold
                               shadow-lg transition"
                    style="background: #dc2626;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== GLOBAL SCRIPTS ==================== --}}
    <script>
        window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        window.api = {
            async _request(method, url, body = null) {
                const headers = {
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'Accept': 'application/json',
                };

                const opts = {
                    method,
                    headers,
                };

                if (body !== null) {
                    const isFormData = body instanceof FormData;

                    if (isFormData) {
                        opts.body = body;
                    } else {
                        headers['Content-Type'] = 'application/json';
                        opts.body = JSON.stringify(body);
                    }
                }

                const res = await fetch(url, opts);

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    let msg = err.message || 'Request gagal: ' + res.status;

                    if (err.errors) {
                        const details = Object.entries(err.errors)
                            .map(([field, msgs]) => `${field}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`)
                            .join(' | ');
                        if (details) msg = details;
                    }

                    throw new Error(msg);
                }
                if (res.status === 204) return {};

                return res.json().catch(() => ({}));
            },

            post(url, body = {}) {
                return this._request('POST', url, body);
            },

            put(url, body = {}) {
                return this._request('PUT', url, body);
            },

            patch(url, body = {}) {
                return this._request('PATCH', url, body);
            },

            delete(url, body = {}) {
                return this._request('DELETE', url, body);
            },

            async get(url) {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Request gagal: ' + res.status);
                return res.json();
            }
        };
        window.showToast = function(message, type = 'success', duration = 3500) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const config = {
                'success': {
                    bg: 'bg-emerald-600',
                    border: 'border-emerald-400/40',
                    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
                },
                'error': {
                    bg: 'bg-red-600',
                    border: 'border-red-400/40',
                    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />`,
                },
                'warning': {
                    bg: 'bg-amber-500',
                    border: 'border-amber-400/40',
                    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`,
                },
                'info': {
                    bg: 'bg-blue-600',
                    border: 'border-blue-400/40',
                    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`,
                },
            };

            const c = config[type] || config.info;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto toast-enter
                flex items-center gap-3
                min-w-[280px] max-w-sm
                px-4 py-3 rounded-xl text-white
                shadow-2xl border backdrop-blur-md
                ${c.bg} ${c.border}`;

            toast.innerHTML = `
                <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        ${c.icon}
                    </svg>
                </div>
                <div class="flex-1 text-sm font-medium leading-snug">${message}</div>
                <button type="button" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;

            toast.querySelector('button').addEventListener('click', () => removeToast(toast));
            container.appendChild(toast);
            setTimeout(() => removeToast(toast), duration);
        };

        function removeToast(toast) {
            if (!toast || !toast.parentNode) return;
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-leave');
            setTimeout(() => toast.remove(), 200);
        }
        window.showConfirm = function(options = {}) {
            return new Promise((resolve) => {
                const {
                    title = 'Konfirmasi',
                        subtitle = 'Perlu tindakan Anda',
                        message = 'Yakin ingin melanjutkan?',
                        okText = 'Ya, Lanjutkan',
                        cancelText = 'Batal',
                        type = 'danger',
                        icon = null,
                } = options;

                const config = {
                    'danger': {
                        bg: 'bg-gradient-to-br from-red-500 to-rose-600',
                        btn: 'background: linear-gradient(to bottom right, #dc2626, #e11d48);',
                    },
                    'warning': {
                        bg: 'bg-gradient-to-br from-amber-500 to-orange-600',
                        btn: 'background: linear-gradient(to bottom right, #d97706, #ea580c);',
                    },
                    'info': {
                        bg: 'bg-gradient-to-br from-indigo-500 to-violet-600',
                        btn: 'background: linear-gradient(to bottom right, #6366f1, #8b5cf6);',
                    },
                    'success': {
                        bg: 'bg-gradient-to-br from-emerald-500 to-teal-600',
                        btn: 'background: linear-gradient(to bottom right, #059669, #0d9488);',
                    },
                };

                const c = config[type] || config.danger;

                const modal = document.getElementById('confirm-modal');
                const box = document.getElementById('confirm-box');
                const header = document.getElementById('confirm-header');
                const titleEl = document.getElementById('confirm-title');
                const subtitleEl = document.getElementById('confirm-subtitle');
                const messageEl = document.getElementById('confirm-message');
                const okBtn = document.getElementById('confirm-ok-btn');
                const cancelBtn = document.getElementById('confirm-cancel-btn');
                const backdrop = document.getElementById('confirm-backdrop');
                const iconEl = document.getElementById('confirm-icon');

                titleEl.textContent = title;
                subtitleEl.textContent = subtitle;
                messageEl.innerHTML = message;
                okBtn.textContent = okText;
                cancelBtn.textContent = cancelText;

                header.className = `px-6 py-5 text-white flex items-start gap-3 ${c.bg}`;
                okBtn.style.cssText = c.btn + ' box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);';

                if (icon) iconEl.innerHTML = icon;

                modal.style.display = 'flex';
                setTimeout(() => {
                    box.classList.remove('scale-95', 'opacity-0');
                    box.classList.add('scale-100', 'opacity-100');
                }, 10);

                const cleanup = () => {
                    box.classList.add('scale-95', 'opacity-0');
                    box.classList.remove('scale-100', 'opacity-100');
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 200);
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                    backdrop.removeEventListener('click', onCancel);
                    document.removeEventListener('keydown', onEsc);
                };

                const onOk = () => {
                    cleanup();
                    resolve(true);
                };

                const onCancel = () => {
                    cleanup();
                    resolve(false);
                };

                const onEsc = (e) => {
                    if (e.key === 'Escape') onCancel();
                };

                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
                backdrop.addEventListener('click', onCancel);
                document.addEventListener('keydown', onEsc);
            });
        };
        document.querySelectorAll('[data-auto-submit]').forEach(el => {
            el.addEventListener('change', () => el.closest('form').submit());
        });
    </script>

    @stack('scripts')

    {{-- ============ PWA SERVICE WORKER ============ --}}
    @pwaSw
    {{-- ============================================ --}}
</body>

</html>
