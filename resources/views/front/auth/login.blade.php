<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front Login | Fox & Mandal</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: url("{{ asset('front/images/library-bg.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(20, 10, 0, 0.85);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);
        }

        .login-card img.logo {
            width: 180px;
            height: auto;
            margin-bottom: 25px;
            border-radius: 12px;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }

        .login-card h4 {
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .login-card p {
            color: #d9b363;
            margin-bottom: 35px;
            font-size: 15px;
        }

        .input-group-text {
            border: 1px solid #d9b363 !important;
            background: rgba(255,255,255,0.05);
            border-radius: 8px 0 0 8px;
        }

        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid #d9b363;
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: #fff;
            font-size: 15px;
        }

        .form-control::placeholder {
            color: #cbb588;
        }

        .form-control:focus {
            background: rgba(255,255,255,0.1);
            color: #fff;
            box-shadow: none;
            border-color: #f0d18a;
        }

        .btn-login {
            background: #f5ede1;
            color: #3d2504;
            font-weight: 700;
            border-radius: 10px;
            width: 100%;
            padding: 12px;
            transition: 0.3s;
            font-size: 16px;
            margin-top: 5px;
        }

        .btn-login:hover {
            background: #e8d6b3;
            color: #3d2504;
        }

        small.text-muted {
            color: #c4c4c4 !important;
        }

        .alert {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .login-card {
                max-width: 90%;
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">
        {{-- Logo --}}
        <img src="{{ asset('backend/images/FMLogo.png') }}" class="logo" alt="Fox & Mandal Logo">

        {{-- Headings --}}
        <h4>Welcome</h4>
        <p>Please login to your account</p>

        {{-- Messages --}}
        @if(session('error'))
            <div class="alert alert-danger py-1">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success py-1">{{ session('success') }}</div>
        @endif

        {{-- Step 1: Enter Mobile --}}
        @if(!session('otp_sent'))
            <form method="POST" action="{{ route('front.send.otp') }}">
                @csrf
                <div class="input-group mb-3">
                    <span class="input-group-text text-light">
                        <i class="fa fa-phone"></i>
                    </span>
                    <input type="text" name="mobile" class="form-control" placeholder="Enter your phone number" required>
                </div>
                @error('mobile') <small class="text-danger">{{ $message }}</small> @enderror

                <button type="submit" class="btn btn-login">Login</button>

                <small class="d-block text-center text-muted mt-3">
                    Enter your phone number in order to receive your security code.
                </small>
            </form>
        @else
            {{-- Step 2: Verify OTP --}}
            <form method="POST" action="{{ route('front.verify.otp') }}">
                @csrf
                <div class="input-group mb-3">
                    <span class="input-group-text text-light">
                        <i class="fa fa-phone"></i>
                    </span>
                    <input type="text" name="mobile" class="form-control" value="{{ session('mobile') }}" readonly>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text text-light">
                        <i class="fa fa-key"></i>
                    </span>
                    <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
                </div>
                @error('otp') <small class="text-danger">{{ $message }}</small> @enderror

                <button type="submit" class="btn btn-login">Verify OTP</button>

                <small class="d-block text-muted mt-2">
                    OTP (for testing): <b>{{ session('otp') }}</b>
                </small>
            </form>
        @endif
    </div>

</body>
</html>
