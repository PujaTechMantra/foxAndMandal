<!DOCTYPE html>
<html>
<head>
    <title>Bus Booking Request</title>
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
        <h2>Bus Booking Request</h2>
        <p style="text-align: center; font-weight: bold;">
            Please share your ID proof to Travel desk / Admin Department
        </p>
        <p>Dear {{ $name }},</p>
        <p>Your bus booking request has been submitted.</p>
        <div class="table-scroll">


            <div class="">
                <div class=""  style="font-weight: bold;">Booking Details</div>
                
            </div>

               
                    @php
                        $travellers = explode(',', $busBooking->traveller);
                        $seatPreferences = explode(',', $busBooking->seat_preference ?? '');
                        $foodPreferences = explode(',', $busBooking->food_preference ?? '');

                        $formattedTravellers = [];
                        foreach ($travellers as $i => $traveller) {
                            if (trim($traveller) !== '') {
                                $formattedTravellers[] = [
                                    'name' => trim($traveller),
                                    'seat_preference' => $seatPreferences[$i] ?? 'N/A',
                                    'food_preference' => $foodPreferences[$i] ?? 'N/A',
                                ];
                            }
                        }
                    @endphp
                    
                              @if (count($formattedTravellers) > 0)
                                    <table>
                                  
                                        <tr>
                                            <td>Booking No</td>
                                            <td rowspan="">{{ $busBooking->order_no }}</td>
                                        </tr>

                                        <tr>
                                            <td>From</td>
                                            <td rowspan="">{{ $busBooking->from }}</td>
                                        </tr>

                                        <tr>
                                            <td>To</td>
                                            <td rowspan="">{{ $busBooking->to }}</td>
                                        </tr>

                                        <tr>
                                            <td>Trip Type</td>
                                            <td rowspan="">
                                                {{ $busBooking->trip_type == 1 ? 'One way' : 'Round Trip' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Travel Date & Time</td>
                                            <td rowspan="">{{ $busBooking->travel_date }}</td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Return Date</td>
                                            <td rowspan="">
                                                {{ $busBooking->trip_type == 2 ? ($busBooking->return_date ?? '') : '' }}
                                            </td>
                                        </tr>
                                      
                                            @foreach ($formattedTravellers as $key => $traveller)
                                  
                                        <tr>
                                            <td>Traveller</td>
                                            <td>{{ $traveller['name'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Seat Preference</td>
                                            <td>{{ $traveller['seat_preference'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Food Preference</td>
                                            <td>{{ $traveller['food_preference'] ?? 'N/A' }}</td>
                                        </tr>
                                    
                                          @endforeach
                                        <tr>
                                            <td>Bill to</td>
                                            <td rowspan="">
                                                {{ $busBooking->bill_to == 1 ? 'Firm' : ($busBooking->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}
                                            </td>
                                        </tr>
                                        @if($busBooking->bill_to == 3)
                                        <tr>
                                            <td>Matter Code</td>
                                            <td rowspan="">{{ $busBooking->matter_code }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td>Purpose/Description</td>
                                            <td rowspan="">{{ $busBooking->purpose_description ?? '' }}</td>
                                        </tr>
                                        
                                        
                                        
                                  
                                    </table>
                                
                       
                    @else
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>Booking No</td>
                                        <td>{{ $busBooking->order_no }}</td>
                                    </tr>
                                    <tr>
                                        <td>From</td>
                                        <td>{{ $busBooking->from }}</td>
                                    </tr>
                                    <tr>
                                        <td>To</td>
                                        <td>{{ $busBooking->to }}</td>
                                    </tr>
                                    <tr>
                                        <td>Trip Type</td>
                                        <td>{{ $busBooking->trip_type == 1 ? 'One way' : 'Round Trip' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Travel Date & Time</td>
                                        <td> {{$busBooking->travel_date }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td>Return Date</td>
                                        <td>{{ $busBooking->trip_type == 2 ? ($busBooking->return_date ?? '') : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Traveller</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Seat Preference</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Food Preference</td>
                                        <td>N/A</td>
                                    </tr>
                                    <tr>
                                        <td>Bill to</td>
                                        <td>{{ $busBooking->bill_to == 1 ? 'Firm' : ($busBooking->bill_to == 2 ? 'Third Party' : 'Matter Expenses') }}</td>
                                    </tr>
                                    @if($busBooking->bill_to == 3)
                                    <tr>
                                        <td>Matter Code</td>
                                        <td>{{ $busBooking->matter_code }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td>Purpose/Description</td>
                                        <td>{{ $busBooking->purpose_description ?? '' }}</td>
                                    </tr>

                                </table>
                            <td>
                        </tr>
                    @endif
                
            </tbody>
        </table>
        </div>
        <p class="footer">Thank you for sharing your request, our team will get back to you within six business hours.</p>
    </div>

</body>
</html>
