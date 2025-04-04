<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deniz Taşımacılığı Matchleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 4.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('profile.edit') }}">Deniz Taşımacılığı</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('gemi_routes.index') }}">Gemi Rotaları</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('yukler.index') }}">Yük İlanları</a>
                    </li>
                    @auth
                        @if(Auth::user()->isGemici())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('gemi_routes.create') }}">Rota Ekle</a>
                            </li>
                        @elseif(Auth::user()->isYukVeren())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('yukler.create') }}">Yük Ekle</a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Üye Ol</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                                @if(Auth::user()->is_admin)
                                    <span class="badge bg-danger">Admin</span>
                                @elseif(Auth::user()->isGemici())
                                    <span class="badge bg-info">Gemici</span>
                                @elseif(Auth::user()->isYukVeren())
                                    <span class="badge bg-success">Yük Veren</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                @if(Auth::user()->is_admin)
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Paneli</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                                
                                @if(Auth::user()->isGemici())
                                    <li><a class="dropdown-item" href="{{ route('profile.gemi_routes') }}">Rotalarım</a></li>
                                @elseif(Auth::user()->isYukVeren())
                                    <li><a class="dropdown-item" href="{{ route('profile.yukler') }}">Yüklerim</a></li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Çıkış Yap</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Deniz Taşımacılığı Matchleme. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
