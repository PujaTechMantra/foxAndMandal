@extends('front.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4">
        <!-- <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('front.library.dashboard') }}" class="text-decoration-none">
                <div class="module-card">
                    <img src="{{ asset('front/images/books-bg.png')}}" alt="LIBRARY">
                    <h6>LIBRARY</h6>
                </div>
            </a>
        </div> -->

        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('front.travel.dashboard') }}" class="text-decoration-none">
                <div class="module-card">
                    <img src="{{ asset('front/images/travel-bg.png')}}" alt="TRAVEL">
                    <h6>TRAVEL</h6>
                </div>
            </a>
        </div>

        <!-- <div class="col-6 col-md-4 col-lg-3">
            <a href="" class="text-decoration-none">
                <div class="module-card">
                    <img src="{{ asset('front/images/vault-bg.png')}}" alt="CAVITY">
                    <h6>CAVITY</h6>
                </div>
            </a>
        </div> -->
        
        <div class="col-6 col-md-4 col-lg-3">
            <a href="https://erp.fmeoffice.online/login" class="text-decoration-none" target="_blank">
                <div class="module-card">
                    <img src="{{ asset('front/images/erp.png')}}" alt="ERP">
                    <h6>ERP</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="https://fms.fmeoffice.online/cms/login" class="text-decoration-none" target="_blank">
                <div class="module-card">
                    <img src="{{ asset('front/images/cms.png')}}" alt="CMS / PMS">
                    <h6>CMS / PMS</h6>
                </div>
            </a>
        </div>
         <div class="col-6 col-md-4 col-lg-3">
            <a href="https://fms.fmeoffice.online/ildms/login" class="text-decoration-none" target="_blank">
                <div class="module-card">
                    <img src="{{ asset('front/images/ildms.png')}}" alt="ILDMS">
                    <h6>ILDMS</h6>
                </div>
            </a>
        </div>
</div>
@endsection
