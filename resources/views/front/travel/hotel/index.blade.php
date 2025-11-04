@extends('front.layouts.app')

@section('title', 'Accommodation')

@section('content')
<div class="container py-5">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
            <div class="d-flex align-items-center">
                <a href="{{ route('front.travel.dashboard') }}" 
                class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-semibold text-dark">Accommodation</h3>
            </div>

            <div class="mt-3 mt-md-0">
                <a href="{{ route('front.travel.hotel.history')}}" 
                class="btn btn-gold btn-sm d-flex align-items-center">
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Booking History</span>
                </a>
            </div>
        </div>

    <!-- Booking Form -->
    <div class="booking-wrapper mx-auto p-5">
        <form class="booking-form" id="hotelBookingForm" 
              method="POST" 
              action="{{ route('front.travel.hotel.store') }}">
            @csrf

            <input type="hidden" name="user_id" value="{{ Auth::guard('front_user')->id() }}">
            <input type="hidden" name="hotel_type" id="tripTypeInput" value="{{ old('hotel_type', 1) }}">

            <div class="text-center mb-4">
                <div class="trip-type d-inline-flex overflow-hidden">
                    <button type="button" class="trip-btn {{ old('hotel_type', 1) == 1 ? 'active' : '' }}" id="guestBtn">Guest House</button>
                    <button type="button" class="trip-btn {{ old('hotel_type', 1) == 2 ? 'active' : '' }}" id="hotelBtn">Hotel</button>
                </div>
            </div>
           <div class="row">
        <!-- Guest Houses -->
        <div class="col-md-6 mb-4" id="guestHouseDiv">
            <label class="form-label">Guest Houses</label>
            <select name="property_id" class="form-control">
                <option value="">Select Location</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}" 
                        {{ old('property_id') == $property->id ? 'selected' : '' }}>
                        {{ $property->name }}
                    </option>
                @endforeach
            </select>
        </div>

    <!-- Hotels -->
    <div class="col-md-6 mb-4" id="hotelDiv" style="display: none;">
        <label class="form-label">Hotels</label>
        <textarea 
            name="hotel_preference" 
            rows="2" 
            class="form-control" 
            placeholder="Type your hotel preference here">{{ old('hotel_preference') }}</textarea>
    </div>
</div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Check-In Date & Time</label>
                    <input 
                        type="text" 
                        class="form-control datetimepicker" 
                        name="departure_date"
                        value="{{ old('departure_date', now()->format('d-m-Y H:i')) }}"
                    >
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Check-Out Date & Time</label>
                    <input 
                        type="text" 
                        class="form-control datetimepicker" 
                        name="return_date"
                        value="{{ old('return_date', now()->format('d-m-Y H:i')) }}"
                    >
                </div>
            </div>

            <div class="row">

                <div class="col-md-6 mb-4">
                    <label class="form-label d-block">Guests</label>

                    <!-- Guest Type -->
                    <div class="d-flex gap-4 align-items-center flex-wrap mb-3">
                        <span class="fw-semibold">Type:</span>

                        <div class="form-check mb-0">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="guest_type[]" 
                                id="inside" 
                                value="insider"
                                {{ in_array('insider', old('guest_type', [])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="inside">
                                Inside
                            </label>
                        </div>

                        <div class="form-check mb-0">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="guest_type[]" 
                                id="outside" 
                                value="outsider"
                                {{ in_array('outsider', old('guest_type', [])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="outside">
                                Outside
                            </label>
                        </div>
                    </div>

                    <!-- Number of Guests -->
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-semibold">Number of Guests:</span>
                        <select class="form-select w-auto" name="guest_number">
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ old('guest_number') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                 <div class="col-md-6 mb-4">
                    <label class="form-label">Purpose/Description</label>
                    <textarea class="form-control" rows="2" name="purpose" placeholder="Please specify the purpose of booking.">{{ old('purpose') }}</textarea>
                </div>
            </div>
               
             <div class="mb-4">
                <label class="form-label">Food Preference</label>
                <div class="radio-group horizontal">
                    <label><input type="radio" name="food_preference" checked value="Veg" {{ old('food_preference') == 'Veg' ? 'checked' : '' }}> Veg</label>
                    <label><input type="radio" name="food_preference" value="Non-Veg" {{ old('food_preference') == 'Non-Veg' ? 'checked' : '' }}> Non-Veg</label>
                </div>
            </div>

             <div class="mb-4">
                <label class="form-label">Bill To</label>
                <div class="radio-group horizontal">
                    <label><input type="radio" name="bill" checked value="1" {{ old('bill') == 1 ? 'checked' : '' }}> Firm</label>
                    <label><input type="radio" name="bill" value="3" {{ old('bill') == 3 ? 'checked' : '' }}> Matter</label>
                    <label><input type="radio" name="bill" value="2" {{ old('bill') == 2 ? 'checked' : '' }}> Third Party</label>
                </div>
            </div>

            <div class="mb-5" id="remarks-div" style="display: {{ old('bill') == 3 ? 'none' : 'block' }};">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="2" placeholder="Please give your remark.">{{ old('remarks') }}</textarea>
            </div>

            <div class="mb-5" id="matter-div" style="display: {{ old('bill') == 3 ? 'block' : 'none' }};">
                <label class="form-label">Enter Matter Code</label>
                <input class="form-control" id="matterCodeInput" name="matter_code" type="text" value="{{ old('matter_code') }}" placeholder="Type at least 3 characters to search..">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-gold w-50">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Person Modal -->
<div class="modal fade" id="addPersonModal" tabindex="-1" aria-labelledby="addPersonModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Person Name</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="personForm" novalidate>
            <div class="row mb-3">
                <div class="col-md-4">
                <label class="form-label">Title</label>
                <select class="form-select" name="title">
                    <option>Mr.</option>
                    <option>Mrs.</option>
                    <option>Miss</option>
                    <option>Ms.</option>
                    <option>Dr.</option>
                </select>
                </div>
                <div class="col-md-8">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ Auth::guard('front_user')->user()->name ?? 'Guest' }}" placeholder="Enter name of person.">
                <div class="invalid-feedback">Please enter name.</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Seat Preference</label>
                <select class="form-select" name="seatPref">
                <option value="">Select</option>
                <option value="Window">Window</option>
                <option value="Aisle">Aisle</option>
                <option value="Emergency Exit">Emergency Exit</option>
                </select>
                <div class="invalid-feedback">Please choose seat preference.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Food Preference</label>
                <select class="form-select" name="food_preference">
                <option value="">Select</option>
                <option value="Veg">Veg</option>
                <option value="Non-Veg">Non-Veg</option>
                <option value="None">None</option>
                </select>
                <div class="invalid-feedback">Please choose food preference.</div>
            </div>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-gold" id="savePersonBtn">Add</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let personList = [];

    $('#guestBtn').click(function () {
        $(this).addClass('active');
        $('#hotelBtn').removeClass('active');
        $('#return-div').hide();
        $('#tripTypeInput').val(1);
    });

    $('#hotelBtn').click(function () {
        $(this).addClass('active');
        $('#guestBtn').removeClass('active');
        $('#return-div').show();
        $('#tripTypeInput').val(2);
    });

    $('input[name="bill"]').on('change', function () {
        if ($(this).val() === '3') {
            $('#matter-div').show();
            $('#remarks-div').hide();
        } else {
            $('#matter-div').hide();
            $('#remarks-div').show();
        }
    });

    $('#hotelBookingForm').on('submit', function () {
        $('#travellerDataInput').val(JSON.stringify(personList));
    });


    $('#guestBtn').click(function () {
    $(this).addClass('active');
    $('#hotelBtn').removeClass('active');
    $('#tripTypeInput').val(1);

    $('#guestHouseDiv').show();
    $('#hotelDiv').hide();
});

$('#hotelBtn').click(function () {
    $(this).addClass('active');
    $('#guestBtn').removeClass('active');
    $('#tripTypeInput').val(2);

    $('#hotelDiv').show();
    $('#guestHouseDiv').hide();
});

if ($('#tripTypeInput').val() == 2) {
    $('#hotelDiv').show();
    $('#guestHouseDiv').hide();
} else {
    $('#guestHouseDiv').show();
    $('#hotelDiv').hide();
}

});



</script>
@endsection
