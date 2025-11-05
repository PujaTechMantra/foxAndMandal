@extends('front.layouts.app')

@section('title', 'Travel')

@section('content')
<div class="container py-4">
    <!-- Back button and title -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('front.dashboard') }}" 
        class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-dark">Travel</h4>
    </div>


    <!-- Card Grid -->
    <div class="row g-4 justify-content-center">
        <div class="col-6 col-md-3">
            <a href="{{route('front.travel.flight.index')}}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/flight-booking.jpg') }}" class="img-fluid rounded mb-2" alt="Flight">
                        <h6 class="fw-bold text-dark mt-2">FLIGHT</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('front.travel.train.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/trainbus-booking.jpg') }}" class="img-fluid rounded mb-2" alt="Train / Bus">
                        <h6 class="fw-bold text-dark mt-2">TRAIN / BUS</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{route('front.travel.cab.index')}}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/car-booking.jpg') }}" class="img-fluid rounded mb-2" alt="Car">
                        <h6 class="fw-bold text-dark mt-2">CAR</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{route('front.travel.hotel.index')}}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100">
                    <div class="card-body">
                        <img src="{{ asset('front/images/hotel-booking.jpg') }}" class="img-fluid rounded mb-2" alt="Accommodation">
                        <h6 class="fw-bold text-dark mt-2">ACCOMMODATION</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
