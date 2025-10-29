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
                        <h4 class="d-flex">Edit Hotel Booking Detail
                            <a href="{{ url('hotel-booking/list') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('hotel-booking/'.$data->id.'/update') }}" method="POST" class="data-form">
                                    @csrf
                                   
        
                                    
                                     <div class="mb-3">
                                        <label for="">Hotel Type</label>
                                        <select name="hotel_type" id="hotelTypeSelect" class="form-control">
                                            <option value="1" {{ $data->hotel_type == 1 ? 'selected' : '' }}>Guest House</option>
                                            <option value="2" {{ $data->hotel_type == 2 ? 'selected' : '' }}>Hotel</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="propertySelectBox" style="display: none;">
                                        <label for="">Property</label>
                                        <select class="form-select form-select-sm" name="property_id" id="property_id">
                                            <option value="" selected disabled>Select Property</option>
                                            @foreach ($property as $cat)
                                                <option value="{{ $cat->id }}" {{ $data->property_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }} ({{ $cat->address }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="hotelTextBox" style="display: none;">
                                        <label for="">Preferred Accommodation</label>
                                        <input type="text" class="form-control" name="text" value="{{ old('text', $data->text ?? '') }}">
                                    </div>
                                    
                                   
                                    <div class="mb-3">
                                        <label for="">Check In Date</label>
                                        <input type="date" name="checkin_date" value="{{ \Carbon\Carbon::parse($data->checkin_date)->format('Y-m-d') }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Check In Time</label>
                                        <input type="text" name="checkin_time" value="{{ $data->checkin_time ?? ($data->checkin_time) }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Check Out</label>
                                        <input type="datetime-local" name="checkout_date" value="{{ \Carbon\Carbon::parse($data->checkout_date)->format('Y-m-d H:i:s') }}" class="form-control" />
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Guest Type</label>
                                        <input type="text" name="guest_type" value="{{$data->guest_type}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Guest Number</label>
                                        <input type="text" name="guest_number" value="{{$data->guest_number}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Food Preference</label>
                                        <select name="food_preference" id="food_preference" class="form-control">
                                            <option value="Veg" {{ $data->food_preference == 'Veg' ? 'selected' : '' }}>Veg</option>
                                            <option value="Non-Veg" {{ $data->food_preference == 'Non-Veg' ? 'selected' : '' }}>Non-Veg</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Bill To</label>
                                        <select name="bill_to" id="billSelect" class="form-control">
                                            <option value="1" {{ $data->bill_to == 1 ? 'selected' : '' }}>Firm</option>
                                            <option value="2" {{ $data->bill_to == 2 ? 'selected' : '' }}>Third Party</option>
                                            <option value="3" {{ $data->bill_to == 3 ? 'selected' : '' }}>Matter Expenses</option>
                                        </select>

                                    </div>
                                    <div class="mb-3" id="billBox" style="display: none;">
                                        <label for="">Matter Code</label>
                                        <input type="text" id="matterCodeInput" name="matter_code" value="{{ $data->matter->matter_code ?? ''}}" class="form-control" autocomplete="off" />
                                        <!-- <input type="text" name="matter_code" value="{{ old('text', $data->matter_code ?? '') }}" class="form-control" /> -->
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Purpose</label>
                                        <input type="text" name="purpose" value="{{$data->purpose}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Description</label>
                                        <input type="text" name="description" value="{{$data->description}}" class="form-control" />
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
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
   document.addEventListener("DOMContentLoaded", function () {
        const tripType = document.getElementById("tripTypeSelect");
        const returnDateWrapper = document.getElementById("returnDateWrapper");
        const returnDateInput = document.getElementById("returnDateInput");

        function toggleReturnDate() {
            if (tripType.value === "2") {
                returnDateWrapper.style.display = "block";
            } else {
                returnDateWrapper.style.display = "none";
                returnDateInput.value = ""; // Clear return date if One Way selected
            }
        }

        tripType.addEventListener("change", toggleReturnDate);

        // Optional: Trigger the check on page load
        toggleReturnDate();
    });
    
    
    function toggleHotelFields() {
        var type = document.getElementById('hotelTypeSelect').value;
        var propertyBox = document.getElementById('propertySelectBox');
        var hotelBox = document.getElementById('hotelTextBox');
        var propertySelect = document.getElementById('property_id');
        var hotelInput = document.querySelector('input[name="text"]');
        if (type == '1') {
            document.getElementById('propertySelectBox').style.display = 'block';
            document.getElementById('hotelTextBox').style.display = 'none';
            hotelInput.value = ''; 
        } else if (type == '2') {
            document.getElementById('propertySelectBox').style.display = 'none';
            document.getElementById('hotelTextBox').style.display = 'block';
            propertySelect.selectedIndex = 0; // Reset property dropdown
        }
    }

    // On page load
    document.addEventListener('DOMContentLoaded', function () {
        toggleHotelFields();
        document.getElementById('hotelTypeSelect').addEventListener('change', toggleHotelFields);
    });
    
    
   
    function toggleMatterCode() {
        var billTo = document.getElementById('billSelect').value;
        var billBox = document.getElementById('billBox');

        if (billTo == '3') {
            billBox.style.display = 'block';
        } else {
            billBox.style.display = 'none';
            document.querySelector('input[name="matter_code"]').value = ''; // Optional: Clear value
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleMatterCode(); // Run on page load
        document.getElementById('billSelect').addEventListener('change', toggleMatterCode); // Run on change
    });

    $(document).ready(function() {
        $("#matterCodeInput").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ url('cab-booking/matter-code/suggest') }}",
                    data: { query: request.term },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.matter_code + ' (' + item.client_name + ')',
                                value: item.matter_code
                            };
                        }));
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $("#matterCodeInput").val(ui.item.value);
            }
        });
    });
</script>
@endsection