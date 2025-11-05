@extends('front.layouts.app')

@section('title', 'Edit Cab Booking')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
        <div class="d-flex align-items-center">
            <a href="{{ route('front.travel.cab.history') }}" 
               class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0 fw-semibold text-dark">Edit Cab Booking</h3>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="booking-wrapper mx-auto p-5">
        <form class="booking-form" id="cabBookingForm" 
              method="POST" 
              action="{{ route('front.travel.cab.update') }}">
            @csrf

            <input type="hidden" name="order_no" value="{{ $booking->order_no }}">
            <input type="hidden" name="user_id" value="{{ Auth::guard('front_user')->id() }}">
            <input type="hidden" name="trip_type" id="tripTypeInput" value="{{ old('trip_type', $booking->trip_type) }}">
            <!-- <input type="hidden" name="traveller_data" id="travellerDataInput"> -->
            <input type="hidden" name="traveller_data" id="travellerDataInput" 
            value="{{ old('traveller_data') }}">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">From</label>
                    <input type="text" name="from" class="form-control" 
                        value="{{ old('from', $booking->from_location) }}" placeholder="Enter source">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">To</label>
                    <input type="text" name="to" class="form-control"
                        value="{{ old('to', $booking->to_location) }}" placeholder="Enter destination">
                </div>
            </div>

             <div class="row align-items-end">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Travel Date & Pickup Time</label>
                    <div class="d-flex gap-2">
                        <input 
                            type="text" 
                            class="form-control" 
                            id="departure_date" 
                            name="departure_date"
                            placeholder="dd-mm-yyyy"
                            value="{{ old('departure_date', $booking->pickup_date ? \Carbon\Carbon::parse($booking->pickup_date)->format('d-m-Y') : '') }}"
                        >
                        <input 
                            type="time" 
                            class="form-control" 
                            name="departure_time"
                            id="departure_time"
                            value="{{ old('departure_time', $booking->pickup_time ?? now()->format('H:i')) }}">
                    </div>
                </div>
            </div>


            <!-- Travellers -->
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
                    <textarea class="form-control" rows="2" name="purpose" placeholder="Please specify the purpose of booking.">{{ old('purpose', $booking->purpose) }}</textarea>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Please enter cab details/other preference if any.">{{ old('description', $booking->description) }}</textarea>
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
                <textarea class="form-control" name="remarks" rows="2" placeholder="Please give your remark.">{{ old('remarks', $booking->bill_to_remarks ?? '') }}</textarea>
            </div>

            <div class="mb-5" id="matter-div" style="display: {{ old('bill', $booking->bill_to) == 3 ? 'block' : 'none' }};">
                <label class="form-label">Enter Matter Code</label>
                <input class="form-control" name="matter_code" type="text" id="matterCodeInput"
                       value="{{ old('matter_code', optional($booking->matter)->matter_code) }}" placeholder="Type at least 3 characters to search..">
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
              <input type="text" class="form-control" name="name" placeholder="Enter name of person.">
              <div class="invalid-feedback">Please enter name.</div>
            </div>
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

        let valid = name;
        if (!valid) {
            if (!name) $('#addPersonModal input[name="name"]').addClass('is-invalid');
            return;
            }

        personList.push({ id: Date.now(), name : `${title} ${name}`});
        renderPersons();
        $('#addPersonModal').modal('hide');
        $('#personForm')[0].reset();
    });

    // On submit — pass JSON
    $('#cabBookingForm').on('submit', function () {
        $('#travellerDataInput').val(JSON.stringify(personList));
    });

});
   
</script>
@endsection
