<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\Color;
use App\Models\Gearbox;
use App\Models\Make;
use App\Models\RearDoor;
use App\Models\Seat;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Wheelbase;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        $this->middleware('auth');
//    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function vehicleData()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://uk1.ukvehicledata.co.uk/api/datapackage/VehicleData?v=2&api_nullitems=1&auth_apikey=81407faf-5c21-4653-b785-823773791d24&key_VRM=KM12AKK',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $response = json_decode($response, true);
        if ($response){
            $reg = $response['Response']['DataItems']['VehicleRegistration'];
            dd($reg);
            $vehicle = new Vehicle();
            $color = Color::where("name", $reg["Colour"])->first();
            if (!$color){
                $color = new Color();
                $color->name =  $reg["Colour"];
            }
            $color->save();
            $vehicle->color_id =  $color->id;
            $wheel = Wheelbase::where("name", $reg["WheelPlan"])->first();
            if (!$wheel){
                $wheel = new Wheelbase();
                $wheel->name =  $reg["WheelPlan"];
            }
            $wheel->save();
            $vehicle->wheelbase_id =  $wheel->id;
            $door = RearDoor::where("name", $reg["DoorPlan"])->first();
            if (!$door){
                $door = new RearDoor();
                $door->name =  $reg["DoorPlan"];
            }
            $door->save();
            $vehicle->rear_door_id =  $door->id;
            $make = Make::where("name", $reg["Make"])->first();
            if (!$make){
                $make = new Make();
                $make->name =  $reg["Make"];
            }
            $make->save();
            $vehicle->make_id =  $make->id;
            $model = CarModel::where("name", $reg["MakeModel"])->first();
            if (!$model){
                $model = new CarModel();
                $model->name =  $reg["MakeModel"];
            }
            $model->save();
            $vehicle->model_id =  $model->id;
            $gear = Gearbox::where("name", $reg["TransmissionType"])->first();
            if (!$gear){
                $gear = new Gearbox();
                $gear->name =  $reg["TransmissionType"];
            }
            $gear->save();
            $vehicle->gear_box_id =  $gear->id;
            $seat = Seat::where("name", $reg["SeatingCapacity"])->first();
            if (!$seat){
                $seat = new Seat();
                $seat->name =  $reg["SeatingCapacity"];
            }
            $seat->save();
            $vehicle->seat_id =  $seat->id;
            $type = VehicleType::where("name", $reg["VehicleClass"])->first();
            if (!$type){
                $type = new VehicleType();
                $type->name =  $reg["VehicleClass"];
            }
            $type->save();
            $vehicle->vehicle_type_id =  $type->id;
            $vehicle->fuel = $reg["FuelType"];
            $vehicle->date_started = date('Y-m-d',strtotime($reg['DateFirstRegistered']));
            $vehicle->save();
        }

        curl_close($curl);

    }

    public function adminHome()
    {
        return view('admin-home');
    }
}
