@extends('layouts.app')

@section('content')


<div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card data-card mt-3">
                    <div class="card-header">
                        <h4 class="d-flex">Cab Booking Request
                            @can('cab booking list csv export')
                            <a href="{{ route('cab-booking.export.csv',['date_from'=>$request->date_from,'date_to'=>$request->date_to,'bill_to'=>$request->bill_to,'keyword'=>$request->keyword]) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                CSV
                            </a>
                            @endcan
                        </h4>
                         <div class="search__filter mb-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mt-1 mb-0">Showing {{$data->count()}} out of {{$data->total()}} Entries</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-12">
                                            <form action="">
                                                <div class="row">
                                                    <div class="col">
                                                        <input type="date" name="date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_to')}}">
                                                    </div>
                                                    <div class="col">
                                                       <select class="form-select form-select-sm" aria-label="Default select example" name="bill_to" id="bill_to">
                                                            <option value="" selected disabled>Select Bill to</option>
                                                            <option value="1" {{ request('bill_to') == '1' ? 'selected' : '' }}>Company</option>
                                                            <option value="2" {{ request('bill_to') == '2' ? 'selected' : '' }}>Client</option>
                                                             <option value="3" {{ request('bill_to') == '3' ? 'selected' : '' }}>Matter Expenses</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col">
                                                        <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by keyword." value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12 text-end">
                                                        <!--<div class="btn-group books_btn_group">-->
                                                            <button type="submit" class="btn btn-sm btn-cta">
                                                                Filter
                                                            </button>
                            
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                        <!--</div>-->
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th class="index-col">#</th>
                                     <th>Unique Code</th>
                                    <th>Member</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Pickup Date</th>
                                    <th>Pickup Time</th>
                                    <th>Traveller</th>
                                    <th>Bill to</th>
                                    <th>Matter Code</th>
                                    <th>Purpose</th>
                                    <th>Description</th>
                                    <th>Booking Status</th>
                                    <th>Cancellation Reason</th>
                                    <th>Action</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=> $item)
                                @php
                                    $statusText = '';
                                    $statusClass = '';
                                
                                    switch ($item->status) {
                                        case 1:
                                            $statusText = 'Pending';
                                            $statusClass = 'text-warning';
                                            break;
                                        case 2:
                                            $statusText = 'Confirmed';
                                            $statusClass = 'text-primary';
                                            break;
                                        case 3:
                                            $statusText = 'Booked';
                                            $statusClass = 'text-success';
                                            break;
                                        default:
                                            $statusText = 'Cancelled';
                                            $statusClass = 'text-danger';
                                            break;
                                    }
                                @endphp
                                <tr>
                                    <td class="index-col">{{ $index+1 }}</td>
                                     <td>{{ $item->order_no }}</td>
                                    <td><a href="{{ url('members/'.$item->user->id) }}">{{ $item->user->name }}</a></td>
                                    <td>{{ $item->from_location }}</td>
                                    <td>{{ $item->to_location }}</td>
                                    <td>{{ $item->pickup_date }}</td>
                                    <td>{{ $item->pickup_time }}</td>
                                    <td>{{ $item->traveller }}</td>
                                    <td>{{ $item->bill_to == 1 ? 'Firm' : ($item->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}</td>
                                    <td>{{ $item->matter->matter_code ?? '' }}</td>
                                    <td>{{ $item->purpose??'' }}</td>
                                    <td>{{ $item->description??'' }}</td>
                                    <td><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                                     @if($item->status==4)
                                     <td>{{ $item->cancellation_remarks ??''}}</td>
                                     @else
                                     <td></td>
                                     @endif
                                    <td style="white-space: nowrap;">
                                        @can('cab booking detail')
                                            <a href="{{ url('cab-booking/details/'.$item->id) }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M508.745 246.041c-4.574-6.257-113.557-153.206-252.748-153.206S7.818 239.784 3.249 246.035a16.896 16.896 0 0 0 0 19.923c4.569 6.257 113.557 153.206 252.748 153.206s248.174-146.95 252.748-153.201a16.875 16.875 0 0 0 0-19.922zM255.997 385.406c-102.529 0-191.33-97.533-217.617-129.418 26.253-31.913 114.868-129.395 217.617-129.395 102.524 0 191.319 97.516 217.617 129.418-26.253 31.912-114.868 129.395-217.617 129.395z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M255.997 154.725c-55.842 0-101.275 45.433-101.275 101.275s45.433 101.275 101.275 101.275S357.272 311.842 357.272 256s-45.433-101.275-101.275-101.275zm0 168.791c-37.23 0-67.516-30.287-67.516-67.516s30.287-67.516 67.516-67.516 67.516 30.287 67.516 67.516-30.286 67.516-67.516 67.516z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                        @endcan
                                        @can('update cab booking')
                                            <a href="{{ url('cab-booking/'.$item->id.'/edit') }}" class="btn btn-cta">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 492.493 492" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304.14 82.473 33.165 353.469a10.799 10.799 0 0 0-2.816 4.949L.313 478.973a10.716 10.716 0 0 0 2.816 10.136 10.675 10.675 0 0 0 7.527 3.114 10.6 10.6 0 0 0 2.582-.32l120.555-30.04a10.655 10.655 0 0 0 4.95-2.812l271-270.977zM476.875 45.523 446.711 15.36c-20.16-20.16-55.297-20.14-75.434 0l-36.949 36.95 105.598 105.597 36.949-36.949c10.07-10.066 15.617-23.465 15.617-37.715s-5.547-27.648-15.617-37.719zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                            </a>
                                        @endcan
                                    </td>
                                    <!--<td>Help</td>-->
                                </tr>
                                @empty
                                     <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                         {{ $data->appends($_GET)->render() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection