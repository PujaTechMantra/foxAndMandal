@extends('front.layouts.app')

@section('title', 'Flight Booking')

@section('content')


<div class="container py-5">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('front.travel.dashboard') }}" 
               class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0 fw-semibold text-dark">Flight Booking</h3>
        </div>

        <button class="refresh-btn d-flex align-items-center justify-content-center" type="button">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <!-- Booking Form -->
    <div class="booking-wrapper mx-auto p-5">
        <form class="booking-form" id="flightBookingForm">
              @csrf
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">From</label>
                    <input type="text" name="from" class="form-control" placeholder="Enter source">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">To</label>
                    <input type="text" name="to" class="form-control" placeholder="Enter destination">
                </div>
            </div>

            <div class="text-center mb-4">
                <div class="trip-type d-inline-flex overflow-hidden">
                    <button type="button" class="trip-btn active" id="oneWayBtn">One Way</button>
                    <button type="button" class="trip-btn" id="roundTripBtn">Round Trip</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Departure Date</label>
                    <input type="date" class="form-control" name="departure_date">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Departure Time</label>
                    <div class="radio-group">
                        <label><input type="radio" name="departure-time" value="12 am - 8 am"> Early Morning <span>12 am - 8 am</span></label>
                        <label><input type="radio" name="departure-time" value="8 am - 12 pm"> Morning <span>8 am - 12 pm</span></label>
                        <label><input type="radio" name="departure-time" value="12 pm - 4 pm"> Mid-day <span>12 pm - 4 pm</span></label>
                        <label><input type="radio" name="departure-time" value="4 pm - 8 pm"> Evening <span>4 pm - 8 pm</span></label>
                        <label><input type="radio" name="departure-time" value="8 pm - 12 am"> Night <span>8 pm - 12 am</span></label>
                    </div>
                </div>
            </div>

            <!-- Return section -->
            <div class="row" id="return-div" style="display: none;">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Return Date</label>
                    <input type="date" class="form-control" name="return_date">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Return Time</label>
                    <div class="radio-group">
                        <label><input type="radio" name="return-time" value="12 am - 8 am"> Early Morning <span>12 am - 8 am</span></label>
                        <label><input type="radio" name="return-time" value="18 am - 12 pm"> Morning <span>8 am - 12 pm</span></label>
                        <label><input type="radio" name="return-time" value="12 pm - 4 pm"> Mid-day <span>12 pm - 4 pm</span></label>
                        <label><input type="radio" name="return-time" value="4 pm - 8 pm"> Evening <span>4 pm - 8 pm</span></label>
                        <label><input type="radio" name="return-time" value="8 pm - 12 am"> Night <span>8 pm - 12 am</span></label>
                    </div>
                </div>
            </div>

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
                    <textarea class="form-control" rows="2" name="purpose" placeholder="Please specify the purpose of booking."></textarea>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Enter flight details or preferences."></textarea>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Bill To</label>
                <div class="radio-group horizontal">
                    <label><input type="radio" name="bill" value="1"> Firm</label>
                    <label><input type="radio" name="bill" value="2"> Matter</label>
                    <label><input type="radio" name="bill" value="3"> Third Party</label>
                </div>
            </div>

            <div class="mb-5" id="remarks-div">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="2" placeholder="Please give your remark"></textarea>
            </div>

            <div class="mb-5" id="matter-div" style="display:none;">
                <label class="form-label">Enter Matter Code</label>
                <input class="form-control" name="matter_code" type="text" placeholder="Type at least 3 characters to search..">
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
        <h5 class="modal-title" id="addPersonModalLabel">Add Person Name</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="personForm">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Title</label>
              <select class="form-select" name="title">
                <option>Mr.</option>
                <option>Mrs.</option>
                <option>Miss</option>
                <option>Ms.</option>
                <option>Dr.</option>
                <option>Mx.</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" value="{{ Auth::guard('front_user')->user()->name ?? 'Guest' }}" placeholder="Enter full name">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Seat Preference</label>
            <select class="form-select" ame="seatPref">
              <option>Select</option>
              <option>Window</option>
              <option>Aisle</option>
              <option>Emergency Exit</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Food Preference</label>
            <select class="form-select" name="foodPref">
              <option>Select</option>
              <option>Veg</option>
              <option>Non-Veg</option>
              <option>None</option>
            </select>
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
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="liveToast" class="toast align-items-center text-bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const apiUrl = "{{ route('front.travel.flight.store') }}";
    const userId = "{{ Auth::guard('front_user')->id() }}";

    let personList = [];

    // Toggle trip type
    $('#oneWayBtn').click(function () {
        $(this).addClass('active');
        $('#roundTripBtn').removeClass('active');
        $('#return-div').hide();
    });

    $('#roundTripBtn').click(function () {
        $(this).addClass('active');
        $('#oneWayBtn').removeClass('active');
        $('#return-div').show();
    });

    // Bill To logic
    $('input[name="bill"]').on('change', function () {
        if ($(this).val() === '2') {
            $('#matter-div').show();
            $('#remarks-div').hide();
        } else {
            $('#matter-div').hide();
            $('#remarks-div').show();
        }
    });

    // Save person modal
    $('#savePersonBtn').click(function () {
        const selects = $('#addPersonModal select');
        const inputs = $('#addPersonModal input');
        const title = $('#addPersonModal select[name="title"]').val();
        const name = $('#addPersonModal input[name="name"]').val().trim();
        const seatPref = $('#addPersonModal select[name="seatPref"]').val();
        const foodPref = $('#addPersonModal select[name="foodPref"]').val();


        if (!name) {
            showToast('Please enter name', 'warning');
            return;
        }

        const person = { id: Date.now(), title, name, seatPref, foodPref };
        personList.push(person);
        renderPersons();
        $('#addPersonModal').modal('hide');
        $('#personForm')[0].reset();
    });

    // Render person cards
    function renderPersons() {
        const $wrapper = $('#personCardWrapper');
        const $body = $('#personCardBody');
        $body.empty();

        if (personList.length === 0) return $wrapper.hide();

        $wrapper.show();
        personList.forEach((p, i) => {
            const cardHtml = `
                ${i > 0 ? '<hr class="my-3 border-gold">' : ''}
                <div class="d-flex justify-content-between align-items-center flex-wrap py-2">
                    <div>
                        <h6 class="mb-1 fw-semibold">${p.title} ${p.name}</h6>
                        <p class="mb-0 text-muted small">
                            <strong>Seat:</strong> ${p.seatPref} &nbsp;|&nbsp;
                            <strong>Food:</strong> ${p.foodPref}
                        </p>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-circle delete-person" data-id="${p.id}" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            $body.append(cardHtml);
        });
    }

    // Delete person
    $(document).on('click', '.delete-person', function () {
        const id = $(this).data('id');
        personList = personList.filter(p => p.id !== id);
        renderPersons();
    });

    // Submit form
    $('#flightBookingForm').on('submit', function (e) {
        e.preventDefault();

        const trip_type = $('#roundTripBtn').hasClass('active') ? 2 : 1;
        const bill = $('input[name="bill"]:checked').val();

        const data = {
            user_id: userId,
            trip_type: trip_type,
            from: $('input[name="from"]').val(),
            to: $('input[name="to"]').val(),
            departure_date: $('input[name="departure_date"]').val(),
            arrival_time: $('input[name="departure-time"]:checked').val(),
            return_date: trip_type === 2 ? $('input[name="return_date"]').val() : null,
            return_time: trip_type === 2 ? $('input[name="return-time"]:checked').val() : null,
            bill_to: $('input[name="bill"]:checked').val(),
            matter_code: bill === '2' ? $('input[name="matter_code"]').val() : null,
            purpose: $('textarea[name="purpose"]').val(),
            description: $('textarea[name="description"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
            traveller: personList.map(p => ({
                name: p.name,
                seat_preference: p.seatPref,
                food_preference: p.foodPref
            }))
        };

        // console.log(data);

       $.ajax({
            url: apiUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF token for Laravel
            },
            data: data,
            success: function (res) {
                if (res.status) {
                    showToast('Flight booking submitted successfully', 'success');
                    $('#flightBookingForm')[0].reset();
                    $('#personCardWrapper').hide();
                    personList = [];

                    setTimeout(() => {
                        window.location.href = "{{ route('front.travel.dashboard') }}";
                    }, 1500);
                } else {
                    showToast('⚠️ ' + res.message, 'warning');
                }
            },
            error: function (xhr) {
                showToast('Something went wrong', 'danger');
            }
        });

    });
});
</script>
@endsection



