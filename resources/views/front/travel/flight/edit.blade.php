@extends('front.layouts.app')

@section('title', 'Edit Flight Booking')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('front.travel.flight.history') }}" 
               class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0 fw-semibold text-dark">Edit Flight Booking</h3>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="booking-wrapper mx-auto p-5">
        <form class="booking-form" id="flightBookingForm" 
              method="POST" 
              action="{{ route('front.travel.flight.update', $booking->order_no) }}">
            @csrf

            <input type="hidden" name="order_no" value="{{ $booking->order_no }}">
            <input type="hidden" name="user_id" value="{{ Auth::guard('front_user')->id() }}">
            <input type="hidden" name="trip_type" id="tripTypeInput" value="{{ old('trip_type', $booking->trip_type) }}">
            <input type="hidden" name="traveller_data" id="travellerDataInput">

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">From</label>
                    <input type="text" name="from" class="form-control" 
                           value="{{ old('from', $booking->from) }}" placeholder="Enter source">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">To</label>
                    <input type="text" name="to" class="form-control"
                           value="{{ old('to', $booking->to) }}" placeholder="Enter destination">
                </div>
            </div>

            <div class="text-center mb-4">
                <div class="trip-type d-inline-flex overflow-hidden">
                    <button type="button" class="trip-btn {{ old('trip_type', $booking->trip_type) == 1 ? 'active' : '' }}" id="oneWayBtn">One Way</button>
                    <button type="button" class="trip-btn {{ old('trip_type', $booking->trip_type) == 2 ? 'active' : '' }}" id="roundTripBtn">Round Trip</button>
                </div>
            </div>

            @php
                $today = now()->format('Y-m-d'); 
                $times = [
                    '12 am - 8 am' => 'Early Morning',
                    '8 am - 12 pm' => 'Morning',
                    '12 pm - 4 pm' => 'Mid-day',
                    '4 pm - 8 pm' => 'Evening',
                    '8 pm - 12 am' => 'Night',
                ];
            @endphp

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Departure Date</label>
                    <input type="date" class="form-control" name="departure_date"
                           value="{{ old('departure_date', $booking->departure_date) }}" min="{{ $today }}">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Departure Time</label>
                    <div class="radio-group">
                        @foreach($times as $value => $label)
                            <label>
                                <input type="radio" name="departure-time" value="{{ $value }}"
                                    {{ old('departure-time', $booking->arrival_time) == $value ? 'checked' : '' }}>
                                {{ $label }} <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Return -->
            <div class="row" id="return-div" style="display: {{ old('trip_type', $booking->trip_type) == 2 ? 'block' : 'none' }};">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Return Date</label>
                    <input type="date" class="form-control" name="return_date"
                           value="{{ old('return_date', $booking->return_date) }}" min="{{ $today }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Return Time</label>
                    <div class="radio-group">
                        @foreach($times as $value => $label)
                            <label>
                                <input type="radio" name="return-time" value="{{ $value }}"
                                    {{ old('return-time', $booking->return_time) == $value ? 'checked' : '' }}>
                                {{ $label }} <span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Travellers -->
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

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Purpose</label>
                    <textarea class="form-control" rows="2" name="purpose">{{ old('purpose', $booking->purpose) }}</textarea>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2">{{ old('description', $booking->description) }}</textarea>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Bill To</label>
                <div class="radio-group horizontal">
                    <label><input type="radio" name="bill" value="1" {{ old('bill', $booking->bill_to) == 1 ? 'checked' : '' }}> Firm</label>
                    <label><input type="radio" name="bill" value="3" {{ old('bill', $booking->bill_to) == 3 ? 'checked' : '' }}> Matter</label>
                    <label><input type="radio" name="bill" value="2" {{ old('bill', $booking->bill_to) == 2 ? 'checked' : '' }}> Third Party</label>
                </div>
            </div>

            <div class="mb-5" id="remarks-div" style="display: {{ old('bill', $booking->bill_to) == 3 ? 'none' : 'block' }};">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="2">{{ old('remarks', $booking->remarks ?? '') }}</textarea>
            </div>

            <div class="mb-5" id="matter-div" style="display: {{ old('bill', $booking->bill_to) == 3 ? 'block' : 'none' }};">
                <label class="form-label">Enter Matter Code</label>
                <input class="form-control" name="matter_code" type="text" 
                       value="{{ old('matter_code', optional($booking->matter)->matter_code) }}">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-gold w-50">Update Booking</button>
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
              <input type="text" class="form-control" name="name" placeholder="Enter name">
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
    let personList = @json($booking->traveller ?? []);

    // Trip type toggle
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

    // Bill to toggle
    $('input[name="bill"]').on('change', function () {
        if ($(this).val() === '3') {
            $('#matter-div').show();
            $('#remarks-div').hide();
        } else {
            $('#matter-div').hide();
            $('#remarks-div').show();
        }
    });

    // Autocomplete (same as add)
    $(function () {
        $('input[name="from"]').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('front.travel.flight.search') }}",
                    data: { term: request.term },
                    success: function (data) { response(data); }
                });
            },
            minLength: 1,
        });

        $('input[name="to"]').autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('front.travel.flight.search') }}",
                    data: { term: request.term },
                    success: function (data) { response(data); }
                });
            },
            minLength: 1,
        });
    });

    // Render existing travellers
    function renderPersons() {
        const $wrapper = $('#personCardWrapper');
        const $body = $('#personCardBody');
        $body.empty();

        if (personList.length === 0) {
            $wrapper.hide();
            return;
        }

        $wrapper.show();
        personList.forEach((p, i) => {
            const cardHtml = `
                ${i > 0 ? '<hr class="my-3 border-gold">' : ''}
                <div class="d-flex justify-content-between align-items-center flex-wrap py-2">
                    <div>
                        <h6 class="mb-1 fw-semibold"> ${p.name ?? ''}</h6>
                        <p class="mb-0 text-muted small">
                            <strong>Seat:</strong> ${p.seat_preference ?? 'N/A'} &nbsp;|&nbsp;
                            <strong>Food:</strong> ${p.food_preference ?? 'N/A'}
                        </p>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-circle delete-person" data-id="${p.id ?? Date.now()}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>`;
            $body.append(cardHtml);
        });
    }


    // Delete handler
    $(document).on('click', '.delete-person', function () {
        const index = $(this).data('index');
        personList.splice(index, 1);
        renderPersons();
    });
    renderPersons();

    // Add person
    $('#savePersonBtn').click(function () {
        $('#personForm .form-control, #personForm .form-select').removeClass('is-invalid');
        let title = $('#addPersonModal select[name="title"]').val();
        let name = $('#addPersonModal input[name="name"]').val().trim();
        let seat_preference = $('#addPersonModal select[name="seatPref"]').val();
        let food_preference = $('#addPersonModal select[name="foodPref"]').val();

        let valid = name && seat_preference && food_preference;
        if (!valid) {
            if (!name) $('#addPersonModal input[name="name"]').addClass('is-invalid');
            if (!seat_preference) $('#addPersonModal select[name="seatPref"]').addClass('is-invalid');
            if (!food_preference) $('#addPersonModal select[name="foodPref"]').addClass('is-invalid');
            return;
        }

        personList.push({ id: Date.now(), name : `${title} ${name}`, seat_preference, food_preference });
        renderPersons();
        $('#addPersonModal').modal('hide');
        $('#personForm')[0].reset();
    });

    // On submit — pass JSON
    $('#flightBookingForm').on('submit', function () {
        $('#travellerDataInput').val(JSON.stringify(personList));
    });
});
</script>
@endsection
