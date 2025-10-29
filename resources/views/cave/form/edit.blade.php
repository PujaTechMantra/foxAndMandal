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
                        <h4 class="d-flex">Edit Cavity
                            <a href="{{ url('vaults') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('vaults/'.$data->id) }}" method="POST" class="data-form" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
        
                                    <div class="mb-3">
                                        <label for="">Location</label>
                                        <select class="form-select form-select-sm" aria-label="Default select example" name="location_id" id="location_id">
                                            <option value="" selected disabled>Select Location/Building</option>
                                            @foreach ($location as $cat)
                                                <option value="{{$cat->id}}" {{$data->location_id == $cat->id ? 'selected' : ''}}> {{$cat->location}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                   
                                    <div class="mb-3">
                                        <label for="">Custodian</label>
                                       <select class="form-select form-select-sm" aria-label="Default select example" name="custodian_id" id="custodian_id">
                                        <option value="" selected disabled>Select Custodian</option>
                                        @foreach ($custodian as $cat)
                                            <option value="{{$cat->id}}" {{$data->custodian_id == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                        @endforeach
                                       </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Cavity Room</label>
                                        <input type="text" name="room" class="form-control" value="{{$data->room}}"  placeholder="Enter Cavity Room"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Unique code</label>
                                        <input type="text" name="unique_code" class="form-control" value="{{$data->unique_code}}"  placeholder="Enter Unique code"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Cavity Sub Location</label>
                                        <input type="text" name="sub_location" class="form-control" value="{{$data->sub_location}}"  placeholder="Enter Cavity Sub Location"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Particulars</label>
                                        <textarea  row="10" name="description" class="form-control">{{$data->description}}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Matter Code</label>
                                        <input type="text" name="client_name" class="form-control" value="{{$data->client_name}}"  placeholder="Enter Matter Code"/>
                                    </div>
                                     <div class="mb-3">
                                        <label for="">Movement</label>
                                        <select class="form-select form-select-sm" aria-label="Default select example" name="movement" id="movement">
                                            <option value="" selected disabled>Select</option>
                                            <option value="in" {{'in'==$data->movement}}>IN</option>
                                            <option value="out" {{'out'==$data->movement}}>OUT</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Remarks</label>
                                        <textarea  row="10" name="remarks" class="form-control">{{$data->remarks}}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        @if(!empty($data->document))
                                        <div class="uploaded-files">
                                            @foreach(explode(',', $data->document) as $file)
                                                <a href="{{ asset($file) }}" target="_blank">
                                                    @if(Str::endsWith($file, ['jpg', 'jpeg', 'png']))
                                                        <span><img src="{{ asset($file) }}" alt="Image"></span>
                                                        <label>{{ basename($file) }}</label>
                                                    @else
                                                        <span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" fill-rule="evenodd" class=""><g><path d="M20.75 20c0 .729-.29 1.429-.805 1.945A2.755 2.755 0 0 1 18 22.75H6c-.729 0-1.429-.29-1.945-.805A2.755 2.755 0 0 1 3.25 20V4c0-.729.29-1.429.805-1.945A2.755 2.755 0 0 1 6 1.25h8.586c.464 0 .909.184 1.237.513l4.414 4.414c.329.328.513.773.513 1.237zm-1.5 0V7.414a.25.25 0 0 0-.073-.177l-4.414-4.414a.25.25 0 0 0-.177-.073H6A1.252 1.252 0 0 0 4.75 4v16A1.252 1.252 0 0 0 6 21.25h12A1.252 1.252 0 0 0 19.25 20z" fill="#0d587f" opacity="1" data-original="#000000" class=""></path><path d="M14.25 2.5a.75.75 0 0 1 1.5 0V6c0 .138.112.25.25.25h3.5a.75.75 0 0 1 0 1.5H16A1.75 1.75 0 0 1 14.25 6zM8 11.25a.75.75 0 0 1 0-1.5h8a.75.75 0 0 1 0 1.5zM8 14.75a.75.75 0 0 1 0-1.5h8a.75.75 0 0 1 0 1.5zM8 18.25a.75.75 0 0 1 0-1.5h4.5a.75.75 0 0 1 0 1.5z" fill="#0d587f" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                        </span>
                                                        <label>{{ basename($file) }}</label>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                        @endif
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
    @else
        OfficeChange({{$data->office_id}})
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
                    @if (request()->input('bookshelves_id')||$data->bookshelves_id)
                        if({{$data->bookshelves_id}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'"'; content+=selected; content += '>'+value.number+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
</script>
@endsection