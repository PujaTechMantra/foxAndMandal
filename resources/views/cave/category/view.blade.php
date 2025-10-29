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

                <div class="card">
                    <div class="card-header">
                        <h4>Cavity Room Details
                            <a href="{{ url('vaultcategories') }}" class="btn btn-danger float-end">Back</a>
                           
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="">
                                 
                                <tr>
                                    <td class="text-muted"> Location: </td>
                                    <td>{{ $data->location->location ??''}}</td>
                                </tr>
                                
                               
                                <tr>
                                    <td class="text-muted">Room Name : </td>
                                    <td>{{$data->name}}</td>
                                </tr>
                                
                                
                                <tr>
                                    <td class="text-muted">Created At: </td>
                                    <td>{{ date('d-m-Y', strtotime($data->created_at)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
               
@endsection


@section('script')

@endsection