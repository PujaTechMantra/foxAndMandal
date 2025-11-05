@extends('front.layouts.app')

@section('title', 'Train/Bus Booking')

@section('content')
<div class="container py-5">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
            <div class="d-flex align-items-center">
                <a href="{{ route('front.travel.dashboard') }}" 
                class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-semibold text-dark">Train/Bus Booking</h3>
            </div>

            <div class="mt-3 mt-md-0">
                <a href="{{ route('front.travel.train.history')}}" 
                class="btn btn-gold btn-sm d-flex align-items-center">
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Booking History</span>
                </a>
            </div>
        </div>

    <!-- Booking Form -->
    <div class="booking-wrapper mx-auto p-5">
        <form class="booking-form" id="trainBookingForm" 
              method="POST" 
              action="{{ route('front.travel.train.store') }}">
            @csrf

            <input type="hidden" name="user_id" value="{{ Auth::guard('front_user')->id() }}">
            <input type="hidden" name="trip_type" id="tripTypeInput" value="{{ old('trip_type', 1) }}">
            <!-- <input type="hidden" name="traveller_data" id="travellerDataInput"> -->
            <input type="hidden" name="traveller_data" id="travellerDataInput" 
            value="{{ old('traveller_data') }}">

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">From</label>
                    <input type="text" name="from" class="form-control" 
                           value="{{ old('from') }}" placeholder="Enter source">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">To</label>
                    <input type="text" name="to" class="form-control"
                           value="{{ old('to') }}" placeholder="Enter destination">
                </div>
            </div>

            <div class="text-center mb-4">
                <div class="trip-type d-inline-flex overflow-hidden">
                    <button type="button" class="trip-btn {{ old('trip_type', 1) == 1 ? 'active' : '' }}" id="oneWayBtn">One Way</button>
                    <button type="button" class="trip-btn {{ old('trip_type', 1) == 2 ? 'active' : '' }}" id="roundTripBtn">Return Trip</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Date of Travel</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="departure_date" 
                        name="departure_date"
                        placeholder="dd-mm-yyyy"
                        value="{{ old('departure_date', now()->format('d-m-Y')) }}" 
                    >
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Time of Travel</label>
                    <div class="radio-group">
                        @php
                            $times = [
                                '12 am - 8 am' => 'Early Morning',
                                '8 am - 12 pm' => 'Morning',
                                '12 pm - 4 pm' => 'Mid-day',
                                '4 pm - 8 pm' => 'Evening',
                                '8 pm - 12 am' => 'Night',
                            ];
                        @endphp
                        @foreach($times as $value => $label)
                            <label>
                                <input type="radio" name="departure_time" value="{{ $label . ' ' . $value }}"
                                    {{ old('departure_time') == $value ? 'checked' : '' }}>
                                {{ $label }} <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Return section -->
            <div class="row" id="return-div" style="display: {{ old('trip_type', 1) == 2 ? 'block' : 'none' }};">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Date of Return</label>
                    <input type="text" class="form-control" name="return_date" id="return_date" value="{{ old('return_date', now()->format('d-m-Y')) }}">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Time of Return</label>
                    <div class="radio-group">
                        @foreach($times as $value => $label)
                            <label>
                                <input type="radio" name="return_time" value="{{ $label . ' ' . $value }}"
                                    {{ old('return_time') == $value ? 'checked' : '' }}>
                                {{ $label }} <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

             <div class="mb-4">
                <label class="form-label">Preference</label>
                <div class="radio-group horizontal">
                    <label><input type="radio" name="preference" checked value="1" {{ old('preference') == 1 ? 'checked' : '' }}>Train</label>
                    <label><input type="radio" name="preference" value="2" {{ old('preference') == 2 ? 'checked' : '' }}>Bus</label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Travellers</label>
                    <div class="mb-4" id="personCardWrapper" style="display: none;">
                        <div class="card person-card shadow-sm border-0">
                            <div class="card-body" id="personCardBody"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addPersonModal">
                            + Add Person
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Purpose</label>
                    <textarea class="form-control" rows="2" name="purpose" placeholder="Please specify the purpose of booking.">{{ old('purpose') }}</textarea>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Please enter train details/other preference if any.">{{ old('description') }}</textarea>
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
                    <option value="AC1">AC1</option>
                    <option value="AC2">AC2</option>
                    <option value="AC3">AC3</option>
                    <option value="ACC">ACC</option>
                </select>
                <div class="invalid-feedback">Please choose seat preference.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Food Preference</label>
                <select class="form-select" name="foodPref">
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
    let isTrain = $('input[name="preference"]:checked').val() === '1'; // 1=Train, 2=Bus

    let oldTravellerData = $('#travellerDataInput').val();

    if (oldTravellerData) {
        try {
            const parsed = JSON.parse(oldTravellerData);
            if (Array.isArray(parsed) && parsed.length > 0) {
                personList = parsed;
                renderPersons();
            }
        } catch (e) {
            console.error("Invalid traveller data JSON:", e);
        }
    }

    // Toggle seat & food fields in modal based on Train/Bus
    function togglePreferenceFields() {
        if (isTrain) {
            $('#personForm select[name="seatPref"]').closest('.mb-3').show();
            $('#personForm select[name="foodPref"]').closest('.mb-3').show();
        } else {
            $('#personForm select[name="seatPref"]').closest('.mb-3').hide();
            $('#personForm select[name="foodPref"]').closest('.mb-3').hide();
        }
    }

    togglePreferenceFields();

    // Update preference on change
    $('input[name="preference"]').on('change', function () {
        isTrain = $(this).val() === '1';
        togglePreferenceFields();
        renderPersons(); // Re-render cards to hide seat/food if Bus selected
    });

    $('#oneWayBtn').click(function () {
        $(this).addClass('active');
        $('#roundTripBtn').removeClass('active');
        $('#return-div').hide();
        $('#tripTypeInput').val(1);
    });

    $('#roundTripBtn').click(function () {
        $(this).addClass('active');
        $('#oneWayBtn').removeClass('active');
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

    $('#addPersonModal').on('show.bs.modal', function () {
        const $nameInput = $(this).find('input[name="name"]');
        if (personList.length > 0) {
            $nameInput.val('');
        }
        togglePreferenceFields();
    });

    $('#savePersonBtn').click(function () {
        $('#personForm .form-control, #personForm .form-select').removeClass('is-invalid');

        let title = $('#addPersonModal select[name="title"]').val();
        let name = $('#addPersonModal input[name="name"]').val().trim();
        let seat_preference = $('#addPersonModal select[name="seatPref"]').val();
        let food_preference = $('#addPersonModal select[name="foodPref"]').val();

        let valid = true;

        if (!name) {
            $('#addPersonModal input[name="name"]').addClass('is-invalid');
            valid = false;
        }

        if (isTrain) {
            if (!seat_preference) {
                $('#addPersonModal select[name="seatPref"]').addClass('is-invalid');
                valid = false;
            }
            if (!food_preference) {
                $('#addPersonModal select[name="foodPref"]').addClass('is-invalid');
                valid = false;
            }
        }

        if (!valid) return;

        let person = { 
            id: Date.now(), 
            name : `${title} ${name}`,
        };

        if (isTrain) {
            person.seat_preference = seat_preference;
            person.food_preference = food_preference;
        }

        personList.push(person);
        renderPersons();
        $('#addPersonModal').modal('hide');
        $('#personForm')[0].reset();
        showToast('Person added successfully!', 'success');
    });

    function renderPersons() {
        const $wrapper = $('#personCardWrapper');
        const $body = $('#personCardBody');
        $body.empty();

        if (personList.length === 0) return $wrapper.hide();
        $wrapper.show();

        personList.forEach((p, i) => {
            let extra = '';
            if (isTrain && p.seat_preference && p.food_preference) {
                extra = `<p class="mb-0 text-muted small">
                            <strong>Seat:</strong> ${p.seat_preference} &nbsp;|&nbsp;
                            <strong>Food:</strong> ${p.food_preference}
                         </p>`;
            }

            const cardHtml = `
                ${i > 0 ? '<hr class="my-3 border-gold">' : ''}
                <div class="d-flex justify-content-between align-items-center flex-wrap py-2">
                    <div>
                        <h6 class="mb-1 fw-semibold">${p.name}</h6>
                        ${extra}
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-circle delete-person" data-id="${p.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>`;
            $body.append(cardHtml);
        });
    }

    // Delete person
    $(document).on('click', '.delete-person', function () {
        const id = $(this).data('id');
        personList = personList.filter(p => p.id !== id);
        renderPersons();
    });

    // Submit
    $('#trainBookingForm').on('submit', function () {
        $('#travellerDataInput').val(JSON.stringify(personList));
    });

    // Autocomplete
    $(function () {
        $('input[name="from"], input[name="to"]').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('front.travel.train.search') }}",
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 1
        });
    });
});
</script>
@endsection

