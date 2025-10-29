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
                        <h4 class="d-flex">Edit Cab Booking Detail
                            <a href="{{ url('cab-booking/list') }}" class="btn btn-cta ms-auto">Back</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <form action="{{ url('cab-booking/'.$data->id.'/update') }}" method="POST" class="data-form">
                                    @csrf
                                   
        
                                    
                                    
                                    
                                    <div class="mb-3">
                                        <label for="">Location From</label>
                                        <input type="text" name="from_location" value="{{$data->from_location}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Location To</label>
                                        <input type="text" name="to_location" value="{{$data->to_location}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Pickup Date</label>
                                        <input type="date" name="pickup_date" value="{{ \Carbon\Carbon::parse($data->pickup_date)->format('Y-m-d') }}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Pickup Time</label>
                                        <input type="time" name="pickup_time" value="{{$data->pickup_time}}" class="form-control" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="">Traveller</label>
                                        <input type="text" name="traveller[]" value="{{$data->traveller}}" class="form-control" multiple/>
                                    </div>
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
                                        <!-- <input type="text" id="matterCodeInput" name="matter_code" value="{{ old('matter_code', $data->matter_code ?? '') }}" class="form-control" /> -->
                                        <input type="text" id="matterCodeInput" name="matter_code" value="{{ old('matter_code', $data->matter->matter_code ?? '') }}" class="form-control" autocomplete="off" />
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