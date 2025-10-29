@extends('layouts.app')

@section('content')


<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Create Matter Code
                            <a href="{{ url('matter-code') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('matter-code') }}" method="POST" class="data-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="">Client Name</label>
                                        <input type="text" name="client_name" value="{{ old('client_name') }}" class="form-control" />
                                        @error('client_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Matter Code</label>
                                        <input type="text" name="matter_code" value="{{ old('matter_code') }}" class="form-control" />
                                        @error('matter_code') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Add</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection