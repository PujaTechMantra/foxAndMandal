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
                        <h4 class="d-flex">
                           Bulk issued book Details
                            @can('bulk issued book csv export')
                            <a href="{{ url('bulk-issue/books/export/csv'). '?date_from='.$request->date_from.'&date_to='.$request->date_from.'&office_id=' . $request->office_id.'&bookshelves_id='. $request->bookshelves_id.'&category_id'.$request->category_id.'&keyword'.$request->keyword }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
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
                                        
                                        <div class="col-md-12 text-end">
                                            <form action="">
                                                <div class="row align-items-end">
                                                    <div class="col">
                                                        <input type="date" name="date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('date_to')}}">
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="office_id" id="office_id">
                                                            <option value="" selected disabled>Select Office</option>
                                                            @foreach ($office as $cat)
                                                                <option value="{{$cat->id}}" {{request()->input('office_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}({{$cat->address}})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="bookshelves_id" id="bookshelves">
                                                            <option value="" selected disabled>Select Bookshelve</option>
                                                            
                                                                <option value="">Select Office  first</option>
                                                            
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <select class="form-select form-select-sm" aria-label="Default select example" name="category_id" id="category_id">
                                                            <option value="" selected disabled>Select Category</option>
                                                            @foreach ($category as $cat)
                                                                <option value="{{$cat->id}}" {{request()->input('category_id') == $cat->id ? 'selected' : ''}}> {{$cat->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <input type="search" name="keyword" id="term" class="form-control form-control-sm" placeholder="Search by keyword." value="{{app('request')->input('keyword')}}" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col text-end">
                                                        <!--<div class="btn-group">-->
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
                                    <th class="sl_no index-col">#</th>
                                    <th>UID</th>
                                    <!--<th>Office</th>-->
                                    <!--<th class="bookshelf">Office Location</th>-->
                                    <!--<th class="bookshelf">Bookshelf No</th>-->
                                    <!--<th>Category</th>-->
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th class="bookshelf">Member Name</th>
                                    <!--<th class="bookshelf">Member Mobile</th>-->
                                    <th class="bookshelf">Issued Date</th>
                                    <th>Issue Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $index=> $item)
                                @php
                                 if($item->issue_type=='issue'){
                                    if($item->request_date && date('d-m-Y', strtotime($item->request_date)) !== '1970-01-01'){
                                     $approveDate=date('d-m-Y', strtotime($item->request_date));
                                    }else{
                                      $approveDate= 'N/A' ;
                                    }
                                 }elseif($item->issue_type=='bulk-issue'){
                                    if($item->request_date && date('d-m-Y', strtotime($item->request_date)) !== '1970-01-01'){
                                     $approveDate=date('d-m-Y', strtotime($item->request_date));
                                    }else{
                                      $approveDate= 'N/A' ;
                                    }
                                   
                                 }elseif($item->issue_type=='re-issue'){
                                    if($item->approve_date && date('d-m-Y', strtotime($item->approve_date)) !== '1970-01-01'){
                                     $approveDate=date('d-m-Y', strtotime($item->approve_date));
                                    }else{
                                     $approveDate= 'N/A' ;
                                    }
                                   
                                 }else{
                                     $approveDate='';
                                 }
                                 
                                 if($item->issue_type=='issue'){
                                   $type='Self';
                                 }elseif($item->issue_type=='bulk-issue'){
                                   $type='Bulk Issue';
                                 }elseif($item->issue_type=='re-issue'){
                                   $type='Re Issue';
                                 }
                                @endphp
                                <tr>
                                    <td class="index-col">{{ $index+1 }}</td>
                                    <td><a href="{{ url('books/'.$item->id) }}">{{ $item->uid ??'' }}</a></td>
                                    <!--<td>{{ $item->office->name ??''}}</td>-->
                                    <!--<td>{{ $item->office->address ??''}}</td>-->
                                    <!--<td>{{ $item->bookshelves->number ??'' }}</td>-->
                                    <!--<td>{{ $item->category->name ??''}}</td>-->
                                    <td><a href="{{ url('books/'.$item->id) }}">{{ $item->title }}</a></td>
                                    <td>{{ $item->author ??'' }}</td>
                                    <td><a href="{{ url('members/'.$item->user_id) }}">{{ $item->name ??'' }}</a></td>
                                    <!--<td>{{ $item->mobile ??'' }}</td>-->
                                    <td>{{ $approveDate ??'' }}</td>
                                    <td>{{ $type ??'' }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No record found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                        {!! $data->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>



<div class="modal fade" id="csvModal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Bulk Upload
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url('books/upload/csv') }}" enctype="multipart/form-data">@csrf
                    <input type="file" name="file" class="form-control" accept=".csv">
                    <br>
                    <a href="{{ asset('backend/csv/sample-book.csv') }}">Download Sample CSV</a>
                    <br>
                    <button type="submit" class="btn btn-danger mt-3" id="csvImportBtn">Import <i class="fas fa-upload"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
<script>
    $(document).ready(function() {
        var officeId = '{{ request()->input('office_id') }}';
        var bookshelvesId = '{{ request()->input('bookshelves_id') }}';

        // Check if office_id is present
        if (officeId) {
            OfficeChange(officeId);
        } else if (bookshelvesId) {
            // If office_id is not present but bookshelves_id is, fetch and show the specific bookshelf
            FetchBookshelfById(bookshelvesId);
        }

        
             $('select[name="office_id"]').on('change', function(event) {
            var value = $(this).val();
            OfficeChange(value);
        });
        
    });

    function OfficeChange(value) {
        $.ajax({
            url: `{{ url('/bookshelves/list/officewise') }}/${value}`,
            method: 'GET',
            success: function(result) {
                var slectTag = 'select[name="bookshelves_id"]';
                var displayCollection = "All";
                var bookshelvesId = '{{ request()->input('bookshelves_id') }}';

                var content = `<option value="">${displayCollection}</option>`;
                $.each(result.data, function(key, value) {
                    let selected = (bookshelvesId == value.id) ? 'selected' : '';
                    content += `<option value="${value.id}" ${selected}>${value.number}</option>`;
                });

                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }

    function FetchBookshelfById(bookshelvesId) {
        $.ajax({
            url: `{{ url('/bookshelves/get') }}/${bookshelvesId}`,  // Assumes you have a route to fetch a single bookshelf by ID
            method: 'GET',
            success: function(result) {
                var slectTag = 'select[name="bookshelves_id"]';
                var content = '';

                if (result.data) {
                    content += `<option value="${result.data.id}" selected>${result.data.number}</option>`;
                } else {
                    content += `<option value="" selected>No bookshelf found</option>`;
                }

                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
</script>
@endsection