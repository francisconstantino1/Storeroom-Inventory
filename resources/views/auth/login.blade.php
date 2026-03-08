@php
    $login_bg_images = ['warehouse.png', 'receiving-office.png'];
    $login_bg_urls = array_map(fn ($img) => asset('images/login/' . $img), $login_bg_images);
@endphp
{{--
    Login page — Store Room Inventory System
    Blurred background photo, no top bar. Styles: public/css/login.css
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Room — Inventory Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/franklin-baker-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @php($recaptchaSiteKey = trim((string) config('services.recaptcha.site_key')))
    @if($recaptchaSiteKey !== '')
        {{-- reCAPTCHA v2 checkbox --}}
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
</head>
<body class="login-page">
    <div class="login-bg" id="loginBg" data-images="{{ implode('|', $login_bg_urls) }}" aria-hidden="true"></div>

    <main class="login-main">
        <div class="login-main-inner">
            <div class="login-card">
                <div class="login-card-header">
                    <img src="{{ asset('images/franklin-baker-logo.png') }}" alt="Franklin Baker" class="login-card-logo">
                </div>

                <div class="login-card-body">
                    @if ($errors->any())
                        <div class="login-error">
                            <span class="login-error-icon">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </span>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf
                        <div class="login-field">
                            <label for="username" class="login-label">Username</label>
                            <div class="login-input-wrap">
                                <input
                                    id="username"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your username"
                                >
                                <span class="login-input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="login-field">
                            <label for="password" class="login-label">Password</label>
                            <div class="login-input-wrap">
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                >
                                <span class="login-input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                            </div>
                        </div>
                        @if($recaptchaSiteKey !== '')
                            <div class="login-field" style="margin-top: 10px;">
                                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                            </div>
                        @endif
                        <button type="submit" class="login-btn">Sign In</button>
                    </form>

                </div>
            </div>
            <p class="login-notice">Authorized personnel only. Unauthorized access is prohibited.</p>
        </div>
    </main>

    <script>
        (function() {
            var el = document.getElementById('loginBg');
            if (!el || !el.dataset.images) return;
            var urls = el.dataset.images.split('|').filter(Boolean);
            if (urls.length < 2) return;
            var index = 0;
            el.style.backgroundImage = "url('" + urls[0].trim() + "')";
            setInterval(function() {
                index = (index + 1) % urls.length;
                el.style.backgroundImage = "url('" + urls[index].trim() + "')";
            }, 5000);
        })();
    </script>
</body>
</html>
