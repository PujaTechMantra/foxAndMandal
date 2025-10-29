@extends('front.layouts.app')

@section('content')
<div class="container py-4">
    <!-- Back button and title -->
     <div class="d-flex align-items-center mb-4">
        <a href="{{ route('front.dashboard') }}" 
        class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-dark">Library</h4>
    </div>


    <!-- Card Grid -->
    <div class="row g-4 justify-content-center">
        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/search-library.jpg') }}" class="img-fluid rounded mb-2" alt="Flight">
                        <h6 class="fw-bold text-dark mt-2">SEARCH LIBRARY</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/books-wishlist.jpg') }}" class="img-fluid rounded mb-2" alt="Train / Bus">
                        <h6 class="fw-bold text-dark mt-2">WISHLIST</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/scan-qr.jpg') }}" class="img-fluid rounded mb-2" alt="Car">
                        <h6 class="fw-bold text-dark mt-2">SCAN TO ISSUE</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/issued-books.jpg') }}" class="img-fluid rounded mb-2" alt="Accommodation">
                        <h6 class="fw-bold text-dark mt-2">ISSUED BOOKS</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/requested-books.jpg') }}" class="img-fluid rounded mb-2" alt="Accommodation">
                        <h6 class="fw-bold text-dark mt-2">REQUESTED BOOKS</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/books-history.jpg') }}" class="img-fluid rounded mb-2" alt="Accommodation">
                        <h6 class="fw-bold text-dark mt-2">HISTORY</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
