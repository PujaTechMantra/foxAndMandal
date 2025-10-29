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
                            Outside Cavity List
                            @can('outside vault csv export')
                            <a href="{{ url('outside/vault/export/csv').'?issue_date_from='.$request->issue_date_from.'&issue_date_to='.$request->issue_date_to .'&keyword='.$request->keyword }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
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
                                                        <input type="date" name="issue_date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="issue_date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_to')}}">
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
                                         <th>Unique Code</th>
                                        <th>Location</th>
                                        <th>Custodian Name</th>
                                        <th class="bookshelf">Room</th>
                                        <th class="bookshelf">Particulars</th>
                                        <th>Issued by</th>
                                        <th >Issued on</th>
                                        <th>QR</th>
                                        <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=> $item)
                                
                                <tr>
                                    <td class="index-col">{{ $index + 1 }}</td>
                                     <td>{{$item->vault->unique_code}}</td>
                                        <td>{{ $item->vault->location->location ?? '' }}</td>
                                       <td>{{ $item->vault->custodian->name ??''}}</td>
                                        <td>{{ $item->vault->room ?? '' }}</td>
                                        <td>{{ $item->vault->description ??'' }}</td>
                                        <td>{{ $item->user->name ??''}}</td>
                                        @if($item->status_for_requested_user==1)
                                        <td>{{ date('d-m-Y', strtotime($item->status_change_date)) }}</td>
                                        @else
                                        <td>{{ date('d-m-Y', strtotime($item->request_date)) }}</td>
                                        @endif
                                        <td><!-- QR Code Display -->
                                            <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$item->vault->qrcode}}&height=13&textsize=10&scale=3&includetext" 
                                                 alt="QR Code" 
                                                 style="height: 83px;width:83px" 
                                                 id="qr-{{$item->id}}" 
                                                 data-qrcode="{{ $item->vault->qrcode }}">
                                        
                                            <!-- Print Button -->
                                            <a class="btn btn-sm btn-danger print_btn" data-bs-toggle="tooltip" title="Print QR" onclick="printQRCode('{{ $item->vault->id }}','{{ $item->vault->qrcode }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                                </svg>
                                            </a>
                                        </td>
                                        <td>
                                            @can('take out list')
                                            <a href="{{ url('vaults/'.$item->vault->id.'/takeout/list') }}" class="btn btn-cta">Issued User</a>
                                            @endcan
                                        </td>
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