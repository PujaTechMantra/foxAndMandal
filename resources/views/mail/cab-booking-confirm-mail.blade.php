<!DOCTYPE html>
<html>
<head>
    <title>Cab Booking Confirmation</title>
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
        <h2>Cab Booking Confirmation.</h2>
       
        <p>Dear {{ $name }},</p>
        <p>Your cab booking request has been confirmed.</p>
        <div class="table-scroll">


            <div class="">
                <div class=""  style="font-weight: bold;">Confirmation Details</div>
                
            </div>

               
                    
                    
                                    <table>
                                   
                                        

                                        <tr>
                                            <td>Cab Number</td>
                                            <td rowspan="">{{ $cabBooking->cab_no }}</td>
                                        </tr>

                                        <tr>
                                            <td>Dept. date & time</td>
                                            <td rowspan="">{{ date('d-m-Y H:i',strtotime($cabBooking->date_time)) }}</td>
                                        </tr>

                                        
                                        
                                        
                                        
                                   
                                    </table>
                                
                                    
                
            
        </div>
        <p class="footer">Have a safe and happy journey.</p>
    </div>

</body>
</html>
