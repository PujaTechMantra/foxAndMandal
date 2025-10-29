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
                        <h4 class="d-flex">Edit Train/Bus Booking Detail
                            <a href="{{ url('train-booking/list') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('train-booking/'.$data->id.'/update') }}" method="POST" class="data-form">
                                    @csrf
                                   
        
                                    
                                    <div class="mb-3">
                                        <label for="">Type</label>
                                        <select name="trip_type" id="tripTypeSelect" class="form-control">
                                            <option value="1" {{ $data->type == 1 ? 'selected' : '' }}>Train</option>
                                            <option value="2" {{ $data->type == 2 ? 'selected' : '' }}>Bus</option>
                                           
                                        </select>

                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Location From</label>
                                        <input type="text" name="from" value="{{$data->from}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Location To</label>
                                        <input type="text" name="to" value="{{$data->to}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Trip Type</label>
                                        <select name="trip_type" id="tripTypeSelectTrain" class="form-control">
                                            <option value="1" {{ $data->trip_type == 1 ? 'selected' : '' }}>One Way</option>
                                            <option value="2" {{ $data->trip_type == 2 ? 'selected' : '' }}>Round Trip</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="">Departure Date</label>
                                        <input type="date" name="travel_date"
                                            value="{{ \Carbon\Carbon::parse($data->travel_date)->format('Y-m-d') }}"
                                            class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Departure Time</label>
                                        <input type="text" name="departure_time"
                                            value="{{ $data->departure_time }}"
                                            class="form-control" />
                                    </div>
                                    <div id="trainreturnDateWrapper" style="{{ $data->trip_type == 1 ? 'display:none;' : '' }}">
                                        <div class="mb-3" >
                                            <label for="">Return Date</label>
                                            <input type="date" name="return_date" id="trainreturnDateInput"
                                                value="{{ $data->return_date ? \Carbon\Carbon::parse($data->return_date)->format('Y-m-d') : '' }}"
                                                class="form-control" />
                                        </div>
                                        <div class="mb-3" >
                                            <label for="">Return time</label>
                                            <input type="text" name="return_time" id="trainreturnTimeInput"
                                                value="{{ $data->return_time ?? '' }}"
                                                class="form-control" />
                                        </div>
                                    </div>
                                    
                                    @php
                                        $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Dr.'];
                                        $travellers = explode(',', $data->traveller ?? '');
                                        $seatPreferences = explode(',', $data->seat_preference ?? '');
                                        $foodPreferences = explode(',', $data->food_preference ?? '');
                                    @endphp
                                    
                                    <div id="traveller-container">
                                        @foreach($travellers as $index => $fullTraveller)
                                            @php
                                                $fullTraveller = trim($fullTraveller);
                                                $selectedPrefix = '';
                                                $nameOnly = $fullTraveller;
                                    
                                                foreach ($prefixes as $prefix) {
                                                    if (str_starts_with($fullTraveller, $prefix)) {
                                                        $selectedPrefix = $prefix;
                                                        $nameOnly = trim(str_replace($prefix, '', $fullTraveller));
                                                        break;
                                                    }
                                                }
                                            @endphp
                                    
                                            <div class="row mb-2 traveller-group">
                                                <div class="col-md-3">
                                                    <label>Prefix</label>
                                                    <select name="prefix[]" class="form-control">
                                                        @foreach($prefixes as $prefix)
                                                            <option value="{{ $prefix }}" {{ $selectedPrefix == $prefix ? 'selected' : '' }}>{{ $prefix }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Traveller</label>
                                                    <input type="text" name="traveller[]" class="form-control" value="{{ $nameOnly }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Seat Preference</label>
                                                    <!--<input type="text" name="seat_preference[]" class="form-control" value="{{ $seatPreferences[$index] ?? '' }}">-->
                                                    <select name="seat_preference[]" id="seat_preference" class="form-control">
                                                        <option value="AC1" {{ $seatPreferences[$index] == 'AC1' ? 'selected' : '' }}>AC1</option>
                                                        <option value="AC2" {{ $seatPreferences[$index] == 'AC2' ? 'selected' : '' }}>AC2</option>
                                                        <option value="AC3" {{ $seatPreferences[$index] == 'AC3' ? 'selected' : '' }}>AC3</option>
                                                        <option value="ACC" {{ $seatPreferences[$index] == 'ACC' ? 'selected' : '' }}>ACC</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Food Preference</label>
                                                    <!--<input type="text" name="food_preference[]" class="form-control" value="{{ $foodPreferences[$index] ?? '' }}">-->
                                                    <select name="food_preference[]" id="food_preference" class="form-control">
                                                        <option value="Veg" {{ $foodPreferences[$index] == 'Veg' ? 'selected' : '' }}>Veg</option>
                                                        <option value="Non-Veg" {{ $foodPreferences[$index] == 'Non-Veg' ? 'selected' : '' }}>Non-Veg</option>
                                                        <option value="CPML" {{ $foodPreferences[$index] == 'CPML' ? 'selected' : '' }}>CPML</option>
                                                        <option value="None" {{ $foodPreferences[$index] == 'None' ? 'selected' : '' }}>None</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 mt-1">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.traveller-group').remove()">Remove</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <button type="button" class="btn btn-success btn-sm mb-3" onclick="addTravellerRow()">+ Add More</button>
                                    <div class="mb-3">
                                        <label for="">Bill To</label>
                                        <select name="bill_to" id="MbillSelect" class="form-control">
                                            <option value="1" {{ $data->bill_to == 1 ? 'selected' : '' }}>Firm</option>
                                            <option value="2" {{ $data->bill_to == 2 ? 'selected' : '' }}>Third Party</option>
                                            <option value="3" {{ $data->bill_to == 3 ? 'selected' : '' }}>Matter Expenses</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="MbillBox" style="display: none;">
                                        <label for="">Matter Code</label>
                                        <input type="text" id="matterCodeInput" name="matter_code" value="{{ old('matter_code', $data->matter->matter_code ?? '') }}" class="form-control" autocomplete="off" />
                                        <!-- <input type="text" id="matterCodeInput" name="matter_code" value="{{ old('matter_code', $data->matter_code ?? '') }}" class="form-control" /> -->
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Purpose</label>
                                        <input type="text" name="purpose" value="{{$data->purpose}}" class="form-control" />
                                    </div>
                                      <div class="mb-3">
                                        <label for="">Description</label>
                                        <input type="text" name="description" value="{{$data->description}}" class="form-control" />
                                    </div>
                                    @if($data->status==3)
                                    <div class="mb-3">
                                        <label for="">PNR</label>
                                        <input type="text" name="pnr" value="{{$data->pnr}}" class="form-control" />
                                    </div>
                                    @endif
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
  const traintripType = document.getElementById("tripTypeSelectTrain");
    const trainreturnDateWrapper = document.getElementById("trainreturnDateWrapper");
    const trainreturnDateInput = document.getElementById("trainreturnDateInput");
    const trainreturnTimeInput = document.getElementById("trainreturnTimeInput");

    function toggletrainReturnDate() {
        if (traintripType.value === "2") {
            trainreturnDateWrapper.style.display = "block";
        } else {
            trainreturnDateWrapper.style.display = "none";
            trainreturnDateInput.value = ""; // clear the return date if One Way selected
            trainreturnTimeInput.value = "";
        }
    }

    traintripType.addEventListener("change", toggletrainReturnDate);
    toggletrainReturnDate(); // run on page load
    
    

     function toggleMatterCode() {
        var billTo = document.getElementById('MbillSelect').value;
        var billBox = document.getElementById('MbillBox');
        var matterCodeInput = document.getElementById('matterCodeInput');

        if (billTo === '3') {
            billBox.style.display = 'block';
        } else {
            billBox.style.display = 'none';
            matterCodeInput.value = ''; // Clear value only if needed
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleMatterCode(); // Initial check
        document.getElementById('MbillSelect').addEventListener('change', toggleMatterCode);
    });
    
    
    
    
    function addTravellerRow() {
    let container = document.getElementById('traveller-container');
    let row = document.createElement('div');
    row.classList.add('row', 'mb-2', 'traveller-group');

    row.innerHTML = `
        <div class="col-md-3">
            <label>Prefix</label>
            <select name="prefix[]" class="form-control">
                <option value="Mr.">Mr.</option>
                <option value="Mrs.">Mrs.</option>
                <option value="Ms.">Ms.</option>
                <option value="Dr.">Dr.</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Traveller</label>
            <input type="text" name="traveller[]" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Seat Preference</label>
           
            <select name="seat_preference[]" id="seat_preference" class="form-control">
                <option value="AC1" {{ $seatPreferences[$index] == 'AC1' ? 'selected' : '' }}>AC1</option>
                <option value="AC2" {{ $seatPreferences[$index] == 'AC2' ? 'selected' : '' }}>AC2</option>
                <option value="AC3" {{ $seatPreferences[$index] == 'AC3' ? 'selected' : '' }}>AC3</option>
                 <option value="ACC" {{ $seatPreferences[$index] == 'ACC' ? 'selected' : '' }}>ACC</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Food Preference</label>
           <select name="food_preference[]" id="food_preference" class="form-control">
                <option value="Veg" {{ $foodPreferences[$index] == 'Veg' ? 'selected' : '' }}>Veg</option>
                <option value="Non-Veg" {{ $foodPreferences[$index] == 'Non-Veg' ? 'selected' : '' }}>Non-Veg</option>
                <option value="CPML" {{ $foodPreferences[$index] == 'CPML' ? 'selected' : '' }}>CPML</option>
                <option value="None" {{ $foodPreferences[$index] == 'None' ? 'selected' : '' }}>None</option>
            </select>
        </div>
        <div class="col-12 mt-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.traveller-group').remove()">Remove</button>
        </div>
    `;
    container.appendChild(row);
    }

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