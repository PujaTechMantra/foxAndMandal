@extends('front.layouts.app')

@section('title', 'Front Dashboard')

@section('content')
<div class="row g-4">
    @php
        $modules = [
            ['title' => 'LIBRARY', 'image' => 'books-bg.png', 'url' => '#'],
            ['title' => 'TRAVEL', 'image' => 'travel-bg.png', 'url' => '#'],
            ['title' => 'CAVITY', 'image' => 'vault-bg.png', 'url' => '#'],
            ['title' => 'ERP', 'image' => 'erp.png', 'url' => '#'],
            ['title' => 'CMS / PMS', 'image' => 'cms.png', 'url' => '#'],
            ['title' => 'ILDMS', 'image' => 'ildms.png', 'url' => '#'],
        ];
    @endphp

    @foreach ($modules as $module)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ $module['url'] }}" class="text-decoration-none">
                <div class="module-card">
                    <img src="{{ asset('front/images/' . $module['image']) }}" alt="{{ $module['title'] }}">
                    <h6>{{ $module['title'] }}</h6>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
