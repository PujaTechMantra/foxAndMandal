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
                        <h4 class="d-flex">Create Member
                            <a href="{{ url('members') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('members') }}" method="POST" class="data-form" enctype="multipart/form-data">
                                    @csrf
        
                                    <div class="mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{old('name')}}"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Email</label>
                                        <input type="text" name="email" class="form-control" value="{{old('email')}}"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Mobile</label>
                                        <input type="text" name="mobile" class="form-control" value="{{old('mobile')}}"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Designation</label>
                                        <input type="text" name="designation"  value="{{ old('designation') }}" class="form-control" value="{{old('designation')}}"/>
                                    </div>
                                        <div class="mb-3">
                                            <!-- Communication Medium -->
                                            <h6>User Permission Area:</h6>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="medium[]" 
                                                    value="Lms" 
                                                    id="mediumLMS"
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumLMS">LMS</label>
                                            </div>
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="medium[]" 
                                                    value="Fms" 
                                                    id="mediumFMS"
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumFMS">FMS</label>
                                            </div>
                                            
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input medium-checkbox" 
                                                    type="checkbox" 
                                                    name="medium[]" 
                                                    value="Cave" 
                                                    id="mediumCave"
                                                    onchange="toggleSelectBox()"
                                                >
                                                <label class="form-check-label" for="mediumCave">Cavity</label>
                                            </div>
                                        </div>
                                        
                                        <label for="type" id="selectBoxLabel" style="display: none;">Lms User Type</label>
                                        <select 
                                            class="form-select form-select-sm" 
                                            aria-label="Default select example" 
                                            name="type" 
                                            id="type" 
                                            style="display: none;"
                                        >
                                            <option value="" selected disabled>Select</option>
                                            <option value="normal member">Normal member</option>
                                            <option value="authorized member">Authorized member</option>
                                        </select>
                                        
                                        <label for="cave_type" id="selectBoxLabel1" style="display: none;">Cavity User Type</label>
                                            <select 
                                                class="form-select form-select-sm" 
                                                aria-label="Default select example" 
                                                name="cave_type" 
                                                id="cave_type" 
                                                style="display: none;" 
                                                onchange="toggleProtemSelectBox()"
                                            >
                                                <option value="" selected disabled>Select</option>
                                                <option value="custodian">Custodian</option>
                                                <option value="protem">Protem</option>
                                            </select>
                                            
                                            <!-- Second Dropdown for Protem Details -->
                                            <label for="protem_details" id="protemLabel" style="display: none;">Custodian Details</label>
                                            <select 
                                                class="form-select form-select-sm" 
                                                aria-label="Default select example" 
                                                name="protem_details" 
                                                id="protem_details" 
                                                style="display: none;"
                                            >
                                                    <option value="" selected disabled>Select</option>
                                                    @foreach ($user as $cat)
                                                        <option value="{{$cat->id}}" {{request()->input('protem_details') == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                                    @endforeach
                                            </select>
                                    
                                    <div class="mb-3">
                                        <label for="">Profile Picture</label>
                                        <input type="file" name="image" class="form-control" />
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
    function toggleSelectBox() {
        const isLmsChecked = document.getElementById('mediumLMS').checked;
        const isCaveChecked = document.getElementById('mediumCave').checked;
        
        const selectBox = document.getElementById('type');
        const selectBoxLabel = document.getElementById('selectBoxLabel');
        
        const selectBox1 = document.getElementById('cave_type');
        const selectBoxLabel1 = document.getElementById('selectBoxLabel1');
        
        if (isLmsChecked) {
            selectBox.style.display = 'block';
            selectBoxLabel.style.display = 'block';
        } else {
            selectBox.style.display = 'none';
            selectBoxLabel.style.display = 'none';
        }
        
        if (isCaveChecked) {
            selectBox1.style.display = 'block';
            selectBoxLabel1.style.display = 'block';
        } else {
            selectBox1.style.display = 'none';
            selectBoxLabel1.style.display = 'none';
        }
    }
    
    
     function toggleProtemSelectBox() {
        const caveType = document.getElementById('cave_type').value;
        const protemSelectBox = document.getElementById('protem_details');
        const protemLabel = document.getElementById('protemLabel');
        
        if (caveType === 'protem') {
            protemSelectBox.style.display = 'block';
            protemLabel.style.display = 'block';
        } else {
            protemSelectBox.style.display = 'none';
            protemLabel.style.display = 'none';
        }
    }
</script>


@endsection