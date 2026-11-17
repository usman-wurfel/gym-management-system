<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pdf</title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        body {
            margin: 0 auto;
            max-width: 900px;
            font-family: sans-serif;
            font-size: 7px;
        }
        table, th, td {
            padding: 7px 10px;
        }
    </style>
</head>
<body>

<main>
    <table class="table display nowrap table-bordered" id="clients" style="margin-top: 13px !important">
        <thead>
        <tr>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Dealer</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Price</th>
            @can("vehicle-edit")
                <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Owes</th>

                <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">SIV</th>
            @endcan
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Reg Number</th>
            @if($id == 5)
                <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Salesman</th>
            @endif
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Make</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Model</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Colour</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">WB</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">PS</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Seats</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Side Door</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">ETA</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Extra 1</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Extra 2</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Extra 3</th>
            <th style="border-bottom: 1px solid #919191;width: 50%;font-weight: bold;">Extra 4</th>

        </tr>
        </thead>
        <tbody>
        @if($vehicles)
            @foreach($vehicles as $vehicle)
                <tr>
                    @php
                        $string = $vehicle->supplier->name;
                        $words = explode(" ", $string);
                        $abbreviation = "";

                        foreach ($words as $word) {
                          $abbreviation .= substr($word, 0, 1);
                        }

                    @endphp
                    <td  style="border-bottom: 1px solid #919191;width: 50%;">{{ substr($string, 0, 2) }}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">£{{$vehicle->vehicle_price}}</td>
                    @can("vehicle-edit")
                        <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->owes}}</td>

                        <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->siv}}</td>
                    @endcan
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->reg_number}}</td>
                    @if($id == 5)
                        <td style="border-bottom: 1px solid #919191;width: 50%;">
                            @if($vehicle->saleman)
                                {{$vehicle->saleman->name}}
                            @endif
                        </td>
                    @endif
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->make->name}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->model->name}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->color->name}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->wheelbase->name}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->power->title}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->seat->name}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->side_door_id ? $vehicle->sideDoor->name : ''}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->eta}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->extra1}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->extra2}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->extra3}}</td>
                    <td style="border-bottom: 1px solid #919191;width: 50%;">{{$vehicle->extra4}}</td>

                </tr>
            @endforeach
        @endif
        </tbody>
    </table>


</main>
</body>
</html>

