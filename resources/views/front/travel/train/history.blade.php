@extends('front.layouts.app')

@section('title', 'Train Booking History')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
        <div class="d-flex align-items-center mb-3 mb-md-0">
            <a href="{{ route('front.travel.train.index') }}" 
               class="back-btn d-flex align-items-center justify-content-center me-3 text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0 fw-semibold text-dark">Train Booking History</h3>
        </div>
    </div>

    @forelse($bookings as $booking)
        <div class="card shadow-sm border-0 mb-4 booking-card rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <!-- Train route -->
                <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
                    <h5 class="fw-semibold text-dark mb-0">
                        🚆 {{ $booking->from }} 
                        <i class="bi bi-arrow-right mx-1 text-secondary"></i> 
                        {{ $booking->to }}
                    </h5>
                    <span class="badge px-3 py-2 rounded-pill 
                        {{ $booking->status == 1 ? 'bg-warning text-dark' : 
                        ($booking->status == 2 ? 'bg-primary' : 
                        ($booking->status == 3 ? 'bg-success' : 
                        ($booking->status == 4 ? 'bg-danger' : 'bg-secondary'))) }}">
                        {{ $booking->status == 1 ? 'Pending' : 
                        ($booking->status == 2 ? 'In Progress' : 
                        ($booking->status == 3 ? 'Booked' : 
                        ($booking->status == 4 ? 'Cancelled' : 'Unknown'))) }}
                    </span>
                </div>

                <div class="row gy-3">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Trip Type:</strong> {{ $booking->trip_type == 1 ? 'One Way' : 'Round Trip' }}</p>
                        <p class="mb-2"><strong>Date of Travel:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('d-m-Y') }}</p>
                        <p class="mb-2"><strong>Time of Travel:</strong> {{ $booking->departure_time }}</p>

                        @if($booking->trip_type == 2)
                            <p class="mb-2"><strong>Date of Return:</strong> {{ \Carbon\Carbon::parse($booking->return_date)->format('d-m-Y') }}</p>
                            <p class="mb-2"><strong>Time of Return:</strong> {{ $booking->return_time }}</p>
                        @endif
                        
                        <p class="mb-2"><strong>Preference:</strong> {{ ($booking->type == 1) ? 'Train' : 'Bus' }}</p>

                        <p class="mb-2"><strong>Purpose:</strong> {{ $booking->purpose ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>Description:</strong> {{ $booking->description ?? 'N/A' }}</p>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Bill To:</strong> 
                            {{ $booking->bill_to == 1 ? 'Firm' : ($booking->bill_to == 2 ? 'Third Party' : 'Matter') }}
                        </p>

                        @if($booking->bill_to == 3)
                            <p class="mb-2"><strong>Matter Code:</strong> {{ $booking->matter->matter_code ?? 'N/A' }}</p>
                        @else
                            <p class="mb-2"><strong>Remarks:</strong> {{ $booking->remarks ?? 'N/A' }}</p>
                        @endif

                        <div class="mt-3">
                            <strong>Travellers:</strong>
                            @foreach($booking->traveller as $traveller)
                                <div class="traveller-box mt-2 border rounded-3 p-2 bg-light">
                                    <div><strong>{{ $traveller['title'] ?? '' }} {{ $traveller['name'] ?? '' }}</strong></div>
                                    <div class="small text-muted">
                                        Seat Preference: {{ $traveller['seat_preference'] ?? '—' }} 
                                    </div>
                                    <div class="small text-muted">
                                         Food Preference: {{ $traveller['food_preference'] ?? '—' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($booking->status == 3)
                    <hr class="my-4">

                    <div class="row gy-3 align-items-start">
                        <div class="col-md-6">
                            <p class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                <strong>Ticket :</strong>

                                <a href="{{ !empty($booking->ticket) ? asset($booking->ticket) : '#' }}" 
                                class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm" target="blank">
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <a href="{{ !empty($booking->ticket) ? asset($booking->ticket) : '#' }}" 
                                class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm" download>
                                    <i class="bi bi-download"></i>
                                </a>
                            </p>

                            <p class="mb-2">
                                <strong>PNR :</strong> 
                                <span class="text-dark fw-semibold">{{ $booking->pnr ?? '—' }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>{{ ($booking->type == 1 ? 'Train' : 'Bus')}} Number :</strong> 
                                <span class="text-dark fw-semibold">{{ $booking->train_no ?? '—' }}</span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Dept. Date & Time :</strong>
                                <span class="text-dark fw-semibold ms-1">
                                   {{ \Carbon\Carbon::parse($booking->date_time)->format('d-m-Y H:i:s') }}
                                    
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4 flex-wrap">
                    <a href="{{ route('front.travel.train.edit', $booking->order_no) }}" 
                       class="btn btn-gold btn-sm px-3 mb-2 {{ $booking->status == 4 ? 'disabled' : '' }}">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>

                    <button 
                        class="btn btn-outline-gold btn-sm px-3 mb-2 cancelBtn" 
                        data-order-no="{{ $booking->order_no }}" 
                        data-bs-toggle="modal" 
                        data-bs-target="#cancelModal"
                        {{ $booking->status == 4 ? 'disabled' : '' }}>
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-train-front fs-1"></i>
            <p class="mt-2">No train bookings found.</p>
        </div>
    @endforelse
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4">
      <form method="POST" action="{{ route('front.travel.train.cancel') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Cancel Booking</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="order_no" id="cancelBookingId">
          <p class="mb-3 fw-semibold text-dark">Please enter reason for cancellation:</p>
          <textarea name="remarks" id="cancelReason" class="form-control" placeholder="Write here..." rows="3" required></textarea>
        </div>
        <div class="modal-footer d-flex flex-wrap justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-gold">Send Request</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
    $('.cancelBtn').on('click', function() {
        const orderNo = $(this).data('order-no');
        console.log('Order No:', orderNo); // ✅ Debug
        $('#cancelBookingId').val(orderNo);
        $('#cancelReason').val('');
    });
});
</script>
@endsection
