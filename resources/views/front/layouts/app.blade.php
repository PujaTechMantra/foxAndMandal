<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        /* ======= Header ======= */
        .navbar {
            background-color: #4b1d0d;
            padding: 0.75rem 1rem;
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar-brand span {
            color: #f5d2a8;
            font-weight: 600;
            font-size: 1.1rem;
            margin-left: 8px;
        }

        .navbar .user-info {
            color: #f5d2a8;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .logout-btn {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            transition: 0.3s;
        }

        .logout-btn:hover {
            color: #f5d2a8;
            transform: scale(1.1);
        }

        /* ======= Cards ======= */
        .module-card {
            background: #fff;
            border-radius: 16px;
            text-align: center;
            padding: 25px 15px;
            transition: all 0.35s ease-in-out;
            height: 100%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: 1px solid #f1f1f1;
        }

        .module-card img {
            width: 100%;
            height: 130px;
            object-fit: contain;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }

        .module-card h6 {
            font-weight: 600;
            color: #4b1d0d;
            font-size: 16px;
            margin-top: 10px;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            background: linear-gradient(180deg, #fff 0%, #fdf3ef 100%);
        }

        .module-card:hover img {
            transform: scale(1.05);
        }

        /* ======= Footer ======= */
        footer {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-top: 40px;
            padding-bottom: 25px;
        }

        footer img {
            height: 40px;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .navbar-brand span {
                display: none;
            }
        }
        .gold-logo {
            height: 40px;
            filter: brightness(0) saturate(100%) invert(82%) sepia(18%) saturate(510%) hue-rotate(349deg) brightness(98%) contrast(96%);
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- ======= Header/Navbar ======= --}}
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('front.dashboard') }}">
                <img src="{{ asset('backend/images/logo.png') }}" alt="Logo" class="gold-logo">
            </a>

            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="user-info">
                    Welcome, <strong>{{ Auth::guard('front_user')->user()->name ?? 'Guest' }}</strong>
                </div>
                <form id="logout-form" action="{{ route('front.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

                <a href="#" class="logout-btn" title="Logout"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-power"></i>
                </a>
            </div>
        </div>
    </nav>

    {{-- ======= Page Content ======= --}}
    <div class="container py-4">
        @yield('content')
    </div>

    {{-- ======= Footer ======= --}}
    <footer>
        <img src="{{ asset('backend/images/FMLogo.png') }}" alt="Logo"><br>
        <p class="mb-0">FOX & MANDAL Solicitors & Advocates</p>
        <small>Bengaluru | Kolkata | Mumbai | New Delhi</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
