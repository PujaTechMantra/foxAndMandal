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
                        <h4 class="d-flex">Create Cavity
                            <a href="{{ url('vaults') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('vaults') }}" method="POST" class="data-form" enctype="multipart/form-data">
                                    @csrf
        
                                    <div class="mb-3">
                                        <label for="">Location</label>
                                        <select class="form-select form-select-sm" aria-label="Default select example" name="location_id" id="location_id">
                                            <option value="" selected disabled>Select Location/Building</option>
                                            @foreach ($location as $cat)
                                                <option value="{{$cat->id}}" {{request()->input('location_id') == $cat->id ? 'selected' : ''}}> {{$cat->location}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Cavity Room</label>
                                        <select class="form-control form-control-sm select2" name="room" disabled>
                                            <option value="{{ $request->room }}">Select location first</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Custodian</label>
                                       <select class="form-select form-select-sm" aria-label="Default select example" name="custodian_id" id="custodian_id">
                                        <option value="" selected disabled>Select Custodian</option>
                                        @foreach ($custodian as $cat)
                                            <option value="{{$cat->id}}" {{request()->input('custodian_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                        @endforeach
                                       </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Unique code</label>
                                        <input type="text" name="unique_code" class="form-control" value="{{old('unique_code')}}"  placeholder="Enter Unique code"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Cavity Sub Location</label>
                                        <input type="text" name="sub_location" class="form-control" value="{{old('sub_location')}}"  placeholder="Enter Cavity Sub Location"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Particulars</label>
                                        <textarea  row="10" name="description" class="form-control">{{old('description')}}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Matter Code</label>
                                        <input type="text" name="client_name" class="form-control" placeholder="Enter Matter Code"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Movement</label>
                                        <select class="form-select form-select-sm" aria-label="Default select example" name="movement" id="movement">
                                            <option value="" selected disabled>Select</option>
                                            <option value="in" >IN</option>
                                            <option value="out" >OUT</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Remarks</label>
                                        <textarea  row="10" name="remarks" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">File Upload </label>
                                        <input type="file" name="document[]" class="form-control"  multiple/>
                                    </div>
                                    <div class="text-end mb-3">
                                        <button type="submit" class="btn btn-submit">Save</button>
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


@section('script')
<script>
    $('select[name="office_id"]').on('change', (event) => {
        var value = $('select[name="office_id"]').val();
        OfficeChange(value);
    });
    @if (request()->input('office_id'))
        OfficeChange({{request()->input('office_id')}})
    @endif

    function OfficeChange(value) {
        $.ajax({
            url: '{{url("/")}}/bookshelves/list/officewise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="bookshelves_id"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data, (key, value) => {
                    let selected = ``;
                    @if (request()->input('bookshelves_id'))
                        if({{request()->input('bookshelves_id')}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'"'; content+=selected; content += '>'+value.number+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
    
    
    $('select[name="location_id"]').on('change', (event) => {
        var value = $('select[name="location_id"]').val();
        LocationChange(value);
    });
    @if (request()->input('location_id'))
        LocationChange({{request()->input('location_id')}})
    @endif

    function LocationChange(value) {
        $.ajax({
            url: '{{url("/")}}/room/list/locationwise/'+value,
            method: 'GET',
            success: function(result) {
                var content = '';
                var slectTag = 'select[name="room"]';
                var displayCollection =  "All";

                content += '<option value="" selected>'+displayCollection+'</option>';
                $.each(result.data.room, (key, value) => {
                    let selected = ``;
                    @if (request()->input('room'))
                        if({{request()->input('room')}} == value.name) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.name+'"'; content+=selected; content += '>'+value.name+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
</script>
@endsection