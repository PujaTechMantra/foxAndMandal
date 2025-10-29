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
                        <h4 class="d-flex">Cavity Details
                            <a href="{{ url('vaults') }}" class="btn btn-cta ms-auto">Back</a>
                            <a type="button" onclick="printQRCode('{{ $data->id }}', '{{ $data->unique_code }}','{{ $data->qrcode }}')" class="btn btn-cta">Print QR</a>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-2 col-12"></div>
                            <div class="col-xl-6 col-lg-8 col-12">
                                <div class="">
                                    <table class="table">
                                         
                                        <tr>
                                            <td class="text-muted"> Location: </td>
                                            <td>{{ $data->location->location ??''}}</td>
                                        </tr>
                                        
                                       <tr>
                                            <td class="text-muted"> Custodian Name: </td>
                                            <td>{{ $data->custodian->name ??''}}</td>
                                        </tr>
                                       
                                        <tr>
                                            <td class="text-muted">Room : </td>
                                            <td>{{$data->room}}</td>
                                        </tr>
                                        <!--<tr>-->
                                        <!--    <td class="text-muted">Name : </td>-->
                                        <!--    <td>{{$data->name}}</td>-->
                                        <!--</tr>-->
                                        <tr>
                                            <td class="text-muted">Sub Location: </td>
                                            <td>{{$data->sub_location}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Particulars : </td>
                                            <td>{{$data->description}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Matter Code : </td>
                                            <td>{{ $data->client_name ??''}}</td>
                                        </tr>
                                        
                                       <tr>
                                            <td class="text-muted">Movement : </td>
                                            <td>{{ strtoupper($data->movement) ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Remarks : </td>
                                            <td>{{ $data->remarks ??''}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created At: </td>
                                            <td>{{ date('d-m-Y', strtotime($data->created_at)) }}</td>
                                        </tr>
                                        
                                        @if(!empty($data->document))
                                        <tr>
                                            <td class="text-muted">Files: </td>
                                            <td>
                                                <div class="uploaded-files">
                                                @foreach(explode(',', $data->document) as $file)
                                                    <a href="{{ asset($file) }}" target="_blank">
                                                        @if(Str::endsWith($file, ['jpg', 'jpeg', 'png']))
                                                            <span>
                                                                <img src="{{ asset($file) }}" alt="Image">
                                                            </span> 
                                                            <label>{{ basename($file) }}</label>
                                                        @else
                                                            <span>
                                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" fill-rule="evenodd" class=""><g><path d="M20.75 20c0 .729-.29 1.429-.805 1.945A2.755 2.755 0 0 1 18 22.75H6c-.729 0-1.429-.29-1.945-.805A2.755 2.755 0 0 1 3.25 20V4c0-.729.29-1.429.805-1.945A2.755 2.755 0 0 1 6 1.25h8.586c.464 0 .909.184 1.237.513l4.414 4.414c.329.328.513.773.513 1.237zm-1.5 0V7.414a.25.25 0 0 0-.073-.177l-4.414-4.414a.25.25 0 0 0-.177-.073H6A1.252 1.252 0 0 0 4.75 4v16A1.252 1.252 0 0 0 6 21.25h12A1.252 1.252 0 0 0 19.25 20z" fill="#0d587f" opacity="1" data-original="#000000" class=""></path><path d="M14.25 2.5a.75.75 0 0 1 1.5 0V6c0 .138.112.25.25.25h3.5a.75.75 0 0 1 0 1.5H16A1.75 1.75 0 0 1 14.25 6zM8 11.25a.75.75 0 0 1 0-1.5h8a.75.75 0 0 1 0 1.5zM8 14.75a.75.75 0 0 1 0-1.5h8a.75.75 0 0 1 0 1.5zM8 18.25a.75.75 0 0 1 0-1.5h4.5a.75.75 0 0 1 0 1.5z" fill="#0d587f" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                            </span>
                                                            <label>{{ basename($file) }}</label>
                                                        @endif
                                                    </a>
                                                @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.min.js"></script>
<script>

   $(document).ready(function() {
            $('#basic').on('click', function() {
                $('#print-code').show();
                $('#print-code').printThis({
                importCSS: true,        // Import page CSS
                importStyle: true,      // Import style tags
                loadCSS: "",            // Load an additional CSS file
                pageTitle: "Books Info", // Title for the printed document
                removeInline: false,    // Keep the inline styles
                printDelay: 333,        // Delay before printing to allow images to load
                afterPrint: function() {
                    $('#print-code').hide(); // Hide the table again after printing
                }
            });
            });
        });
        
        
        function printQRCode(itemId,uid,qrText) {
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
                        <img id="qr-code-img" src="${qrSrc}" style="height: 120px; width: 120px;">
                    </div>
                    <div class="uid-text">
                        <img style="width:190px" src="{{asset('backend/images/logo.png')}}">
                        <h2 class="tt">${uid}</h2>
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