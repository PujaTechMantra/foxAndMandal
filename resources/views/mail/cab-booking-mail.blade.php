<!DOCTYPE html>
<html>
<head>
    <title>Cab Booking Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h2 {
            color: #333;
            text-align: center;
        }
      
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #007bff;
            color: white;
            white-space: nowrap;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            color: #333;
            font-weight: bold;
        }
        .table-block {
            display:flex;
            align-items:center;
            
        }
        .table-block .mail-head {
            max-width:140px;
            flex:0 0 140px;
            font-size:15px;
            font-weight:bold;
            color:#000;
            padding:5px;
        }
        .table-block .mail-body {
            flex:1;
            padding:5px;
            font-size:14px;
            color:#000;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Cab Booking Request</h2>
        <p style="text-align: center; font-weight: bold;">
            Please share your ID proof to Travel desk / Admin Department
        </p>
        <p>Dear {{ $name }},</p>
        <p>Your cab booking request has been submitted.</p>
        <div class="table-scroll">


            <div class="">
                <div class=""  style="font-weight: bold;">Booking Details</div>
                
            </div>

                            <table>
                           
                                <tr>
                                    <td>Booking No</td>
                                    <td rowspan="">{{ $cabBooking->order_no }}</td>
                                </tr>

                                <tr>
                                    <td>From</td>
                                    <td rowspan="">{{ $cabBooking->from_location }}</td>
                                </tr>

                                <tr>
                                    <td>To</td>
                                    <td rowspan="">{{ $cabBooking->to_location }}</td>
                                </tr>


                                <tr>
                                    <td>Pickup Date & Time</td>
                                    <td rowspan="">{{ $cabBooking->pickup_date . ' ' . $cabBooking->pickup_time }}</td>
                                </tr>
                                
                                <tr>
                                    <td>Traveller</td>
                                    <td>{{ $cabBooking->traveller ?? 'N/A' }}</td>
                                </tr>
                                
                                <tr>
                                    <td>Bill to</td>
                                    <td rowspan="">
                                        {{ $cabBooking->bill_to == 1 ? 'Firm' : ($cabBooking->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}
                                    </td>
                                </tr>
                                @if($cabBooking->bill_to == 3)
                                <tr>
                                    <td>Matter Code</td>
                                    <td rowspan="">{{ $cabBooking->matter_code }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Purpose/Description</td>
                                    <td rowspan="">{{ $cabBooking->purpose_description ?? '' }}</td>
                                </tr>
                            </table>
                       
                
            
           
        </div>
        <p class="footer">Thank you for sharing your request, our team will get back to you within six business hours.</p>
    </div>

</body>
</html>
