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
    <link href="https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="{{ asset('front/css/style.css') }}" rel="stylesheet">

    <link href="{{ asset('front/css/flight.css') }}" rel="stylesheet">

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
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
        @if (session('success'))
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @elseif (session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @elseif (session('warning'))
            <div class="toast align-items-center text-dark bg-warning border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('warning') }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @elseif ($errors->any())
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ $errors->first() }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script> 
        document.addEventListener('DOMContentLoaded', function () {
            const toastElList = [].slice.call(document.querySelectorAll('.toast'))
            const toastList = toastElList.map(function (toastEl) {
                const toast = new bootstrap.Toast(toastEl, { delay: 4000 })
                toast.show()
                return toast
            })
        })

</script>
    @yield('scripts')
</body>
</html>
