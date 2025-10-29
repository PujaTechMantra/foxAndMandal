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
                        <h4 class="d-flex">Edit Property
                            <a href="{{ url('properties') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('properties/'.$data->id) }}" method="POST" class="data-form">
                                    @csrf
                                    @method('PUT')
        
                                    <div class="mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" value="{{ $data->name }}" class="form-control" />
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Address</label>
                                        <input type="text" name="address"  value="{{ $data->address }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Type</label>
                                        <input type="text" name="type"  value="{{ $data->type }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Rent</label>
                                        <input type="text" name="rent"  value="{{ $data->rent }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Bedrooms</label>
                                        <input type="text" name="bedrooms"  value="{{ $data->bedrooms }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Bathrooms</label>
                                        <input type="text" name="bathrooms"  value="{{ $data->bathrooms }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Floor Area</label>
                                        <input type="text" name="floor_area"  value="{{ $data->floor_area }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Description</label>
                                        <textarea type="text" name="description"  value="{{ $data->description }}" class="form-control"></textarea>
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Update</button>
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