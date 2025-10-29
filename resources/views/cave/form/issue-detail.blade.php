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
                            Take out Details of {{$form->client_name}}({{$form->remarks}})
                            @can('vault take out list csv download')
                            <a href="{{ url('vaults/takeout/list/export/csv/'.$form->id) }}" class="btn btn-sm btn-cta ms-auto" data-bs-toggle="tooltip" title="Export data in CSV">
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
                                                <form class="row align-items-end" action="">
                                                    <div class="col">
                                                        <input type="date" name="issue_date_from" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_from')}}">
                                                    </div>
                                                     <div class="col">
                                                        <input type="date" name="issue_date_to" id="term" class="form-control form-control-sm"  value="{{app('request')->input('issue_date_to')}}">
                                                    </div>
                                                    <div class="col text-end">
                                                        <!--<div class="btn-group">-->
                                                            <button type="submit" class="btn btn-cta btn-sm">
                                                                Filter
                                                            </button>
                        
                                                            <a href="{{ url()->current() }}" class="btn btn-sm btn-cta" data-bs-toggle="tooltip" title="Clear Filter">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                            </a>
                                                            
                                                        <!--</div>-->
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
                                    <!--<th class="sl_no index-col">#</th>-->
                                    <!--<th class="bookshelf">Member Name</th>-->
                                    <!--<th class="bookshelf">Member Mobile</th>-->
                                    <!--<th class="bookshelf">Member Email</th>-->
                                    <!--<th class="bookshelf">Take out date</th>-->
                                    <!--<th class="bookshelf">Returned date</th>-->
                                    <th class="sl_no index-col">#</th>
                                    <th class="bookshelf">Cavity Unique Code</th>
                                    <th class="bookshelf">Cavity Location</th>
                                    <th class="bookshelf">Cavity Room</th>
                                    <th class="bookshelf">Cavity Custodian Name</th>
                                    <th class="bookshelf">Requested By</th>
                                    <th class="bookshelf">Requested Date</th>
                                    <th class="bookshelf">Request Sent To</th>
                                    <th class="bookshelf">Authorized Member Issued On</th>
                                    <th class="bookshelf">Request Accept Date by Member</th>
                                    <th class="bookshelf">Expected Return Date</th>
                                    <th class="bookshelf">Returned By</th>
                                    <th class="bookshelf">Returned Date</th>
                                    <th class="bookshelf">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index=> $item)
                                @php
                                $returnDate=\App\Models\CaveDoc::where('cave_form_id',$item->id)->where('user_id',$item->user->id)->where('scan_status',1)->first();  
                                $expectedReturn = strtotime($item->expected_return_date);
                                $returnDate = strtotime($item->return_date);
                                
                                $overdueDays = 0;
                                if ($returnDate > $expectedReturn) {
                                    $overdueDays = ($returnDate - $expectedReturn) / (60 * 60 * 24);
                                }
                                @endphp
                                <tr>
                                    
                                    <td class="index-col">{{ $index+1 }}</td>
                                    <td>
                                        {{$item->vault->unique_code}}
                                    </td>
                                    <td>{{ $item->vault->location->location ?? ''}}</td>
                                    <td>{{$item->vault->room}}</td>
                                    <td>{{$item->vault->custodian->name ??''}}</td>
                                    @if(!empty($item->request->user))
                                    <td><a href="{{url('members/'.$item->request->user->id)}}">{{ $item->request->user->name ??''}}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if($item->request)
                                    <td>{{date('d-m-Y', strtotime($item->request->created_at))}}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if(!empty($item->request->custodian) || !empty($item->request->protem))
                                    <td><a href="{{url('members/'.$item->request->custodian->id)}}">{{ $item->request->custodian->name }}</a>,<a href="{{url('members/'.$item->request->protem->id)}}">{{ $item->request->protem->name }}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{ date('d-m-Y', strtotime($item->request_date)) }}</td>
                                    @if($item->status_for_requested_user==1)
                                    <td>{{ date('d-m-Y', strtotime($item->status_change_date)) }}</td>
                                    @else
                                    <td>{{ date('d-m-Y', strtotime($item->request_date)) }}</td>
                                    @endif
                                    <td>{{ date('d-m-Y', strtotime($item->expected_return_date)) }}</td>
                                    @if(!empty($item->returnuser))
                                    <td><a href="{{url('members/'.$item->returnuser->id)}}">{{ $item->returnuser->name ??'' }}</a></td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if($item->is_return==1)
                                    <td>{{date('d-m-Y', strtotime( $item->return_date)) }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    @if($overdueDays==0)
                                    <td>The document was returned on time.</td>
                                    @else
                                    <td>The document has been delayed by {{$overdueDays}} days</td>
                                    @endif
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
    $('select[name="office_id"]').on('change', (event) => {
        var value = $('select[name="office_id"]').val();
        OfficeChange(value);
    });
    @if (request()->input('office_id'))
        OfficeChange({{request()->input('office_id')}})
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
                    @if (request()->input('bookshelves_id'))
                        if({{request()->input('bookshelves_id')}} == value.id) {selected = 'selected';}
                    @endif
                    content += '<option value="'+value.id+'"'; content+=selected; content += '>'+value.number+'</option>';
                });
                $(slectTag).html(content).attr('disabled', false);
            }
        });
    }
    
</script>
@endsection