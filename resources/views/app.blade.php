
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="asset-base-url" content="{{ asset('') }}">

    <title inertia>{{ config('app.name', 'StMark') }}</title>

    <!-- 🔥 PRELOADER STYLES -->
    <style>
        #global-preloader {
            position: fixed;
            inset: 0;
            background: linear-gradient(to bottom, #0f172a, #020617);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999999;
        }

        /* Center content */
        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-align: center;
        }

        /* Responsive animation size */
        #lottie-book {
            width: clamp(120px, 15vw, 220px);
            height: clamp(120px, 15vw, 220px);
        }

        /* Loading text */
        .loading-text {
            font-size: clamp(12px, 1.2vw, 16px);
            color: #e2e8f0;
            letter-spacing: 1px;
            animation: fade 1.5s infinite;
        }

        @keyframes fade {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Extra large screens */
        @media (min-width: 1600px) {
            #lottie-book {
                width: 260px;
                height: 260px;
            }

            .loading-text {
                font-size: 18px;
            }
        }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Laravel + Inertia -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">

    <!-- 🔵 GLOBAL PRELOADER -->
    <div id="global-preloader">
        <div class="loader-content">
            <div id="lottie-book"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>

    <!-- 🚀 APP -->
    @inertia

    <!-- ✅ LOTTIE LIBRARY -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.9.6/lottie.min.js"></script>

    <!-- ✅ PRELOADER SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lottie.loadAnimation({
                container: document.getElementById('lottie-book'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '{{ asset("animations/animation.json") }}' // ⚠️ important for your setup
            });
        });

        window.addEventListener('load', function () {
            const loader = document.getElementById('global-preloader');

            if (loader) {
                loader.style.opacity = '0';
                loader.style.transition = 'opacity 0.6s ease';

                setTimeout(() => {
                    loader.remove();
                }, 600);
            }
        });
    </script>

    <!-- Optional external scripts -->
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

</body>
</html>

