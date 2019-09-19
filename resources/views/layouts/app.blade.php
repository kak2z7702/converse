<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Converse') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    @hasSection('scripts')
    @yield('scripts')
    @endif

    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            $(document).on('change', '.custom-file-input', function (event) {
                $(this).next('.custom-file-label').html(event.target.files[0].name);
            });
        });
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @hasSection('styles')
    @yield('styles')
    @endif

    <style>
        .blockquote {
            background: #eeeeee;
            border-left: 5px solid lightsteelblue;
            padding-left: 5px;
        }

        .img-border {
            border: 3px solid #cccccc;
        }
    </style>
</head>
<body class="h-100">
    <div id="app" class="d-flex flex-column h-100">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Converse') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        @foreach ($menus as $menu)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $menu->url }}">{{ $menu->title }}</a>
                        </li>
                        @endforeach
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('user.register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('user.register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            @if (Auth::user()->is_admin)
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ __('Community') }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('user.index') }}">{{ __('Users') }}</a>
                                    <a class="dropdown-item" href="{{ route('role.index') }}">{{ __('Roles') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('menu.index') }}">{{ __('Menus') }}</a>
                                    <a class="dropdown-item" href="{{ route('page.index') }}">{{ __('Pages') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('options') }}">{{ __('Options') }}</a>
                                </div>
                            </li>
                            @endif
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    @if ($new_messages > 0)
                                    <span class="badge badge-pill badge-danger">!</span>
                                    @endif
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('message.index') }}">{{ __('Messages') }} @if ($new_messages > 0)<span class="badge badge-pill badge-danger">{{ $new_messages }}</span>@endif</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('user.profile') }}">{{ __('Profile') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('user.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>

                                    <form id="logout-form" action="{{ route('user.logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if ($is_installed || request()->path() == 'install')
            @yield('content')
            @else
            <div class="container">
                <p>{!! __('Install your community by going to <a href=":url">/install</a> route!', ['url' => route('install')]) !!}</p>
            </div>
            @endif
        </main>

        <footer class="footer mt-auto py-3">
            <div class="container">
                <span class="text-muted">
                    <span style="color: steelblue">Converse</span> Community Manager<br />                    
                    Copyright &copy; 2019 Mahan Heshmati Moghaddam
                </span>
            </div>
        </footer>
    </div>
</body>
</html>
