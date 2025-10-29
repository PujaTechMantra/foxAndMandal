@extends('layouts.app')

@section('content')


<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                @php
                  $permission=DB::table('user_permission_categories')->where('user_id',$user->id)->pluck('name');
                @endphp
                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif

                <div class="card data-card">
                    <div class="card-header">
                        <h4 class="d-flex">Member Detail
                            <a href="{{ url('members') }}" class="btn btn-cta ms-auto">Back</a>
                            @if($user->type=='authorized member')
                            <a type="button" onclick="printQRCode('{{ $user->id }}','{{ $user->qrcode }}')" class="btn btn-cta">Print QR</a>
                            @endif
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <div class="table-responsive">
                                    <table class="table">
                                         <div class="user-info">
                                            <tr>
                                                <td class="text-muted">Name: </td>
                                                <td>{{$user->name}}</td>
                                            </tr>
                                         </div>
                                        <tr>
                                            <td class="text-muted">Email: </td>
                                            <td>{{ $user->email ??''}}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="text-muted">Mobile :  </td>
                                            <td>{{ $user->mobile }}</td>
                                        </tr>
                                         <tr>
                                            <td class="text-muted">Designation :  </td>
                                            <td>{{ $user->designation }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Type :  </td>
                                            <td>{{ $user->type }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">App Permission : </td>
                                            <td>{{ $permission->implode(', ') }}</td>
                                        </tr>
                                        @if($user->type=='authorized member')
                                        <tr>
                                            <td class="text-muted">QR: </td>
                                            <td><img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{$user->qrcode}}&height=6&textsize=10&scale=6&includetext" 
                                                         alt="QR Code" 
                                                         style="height: 105px;width:105px" 
                                                         id="qr-{{$user->id}}" 
                                                         data-qrcode="{{ $user->qrcode }}"></td>
                                        </tr>
                                        @endif
                                         @if(!empty($user->image))
                                        <tr>
                                            <td class="text-muted">Files: </td>
                                            <td>
                                                <div class="uploaded-files">
                                               
                                                    <a href="{{ asset($user->image) }}" target="_blank">
                                                        @if(Str::endsWith($user->image, ['jpg', 'jpeg', 'png']))
                                                            <span>
                                                                <img src="{{ asset($user->image) }}" alt="Image">
                                                            </span> 
                                                            <label>{{ basename($user->image) }}</label>
                                                        @endif
                                                    </a>
                                                
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="text-muted">Created At: </td>
                                            <td>{{ date('d-m-Y', strtotime($user->created_at)) }}</td>
                                        </tr>
                                    </table>
                                </div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- printThis Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
<script>
    function printQRCode(itemId,qrText) {
        // Open a new window for printing
        const printWindow = window.open('', '', 'width=600,height=400');
        const qrSrc = `https://bwipjs-api.metafloor.com/?bcid=qrcode&text=${qrText}&height=6&textsize=10&scale=6&includetext`;

        printWindow.document.write(`
            <html>
            <head>
                <title>Print QR</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                    }
                    .sticker-container {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        justify-content:center;
                        width: 400px;  /* Adjust width to match sticker size */
                        height: 150px; /* Adjust height to match sticker size */
                        margin-top: 20px;
                    }
                    .print-container {
                        margin-top: 20px;
                    }
                    .book-title {
                        font-size: 20px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .qr-code {
                        width: 40%; /* QR code on the left half */
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position:relative;
                        top:10px;
                       
                    }
                    .uid-text {
                        width: 50%; /* UID on the right half */
                        font-size: 17px;
                        
                        position:relative;
                        top:10px;
                        line-height:1;
                        text-align:left;
                        
                    }
                        .uid-text h2 {
                            line-height:0;
                            
                        }
                        .uid-text h2.tt {
                            position:relative;
                            top:10px;
                        }
                   
                </style>
            </head>
            <body>
                <div class="sticker-container">
                    <!-- Book Name -->
                    
                    
                    <!-- QR Code Placeholder -->
                    <div class="qr-code">
                        <img id="qr-code-img" src="${qrSrc}" style="height: 80px; width: 80px;">
                    </div>
                     <div class="uid-text">
                        <img style="width:190px" src="{{asset('backend/images/logo.png')}}">
                        
                    </div>
                </div>
            </body>
            </html>
        `);

        // Wait for the image to load before printing
        const qrCodeImg = printWindow.document.getElementById('qr-code-img');
        qrCodeImg.onload = function () {
            // Once the image is loaded, trigger the print
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };

        // If the image fails to load, close the window
        qrCodeImg.onerror = function () {
            alert('QR code could not be loaded.');
            printWindow.close();
        };
    }
</script>
@endsection