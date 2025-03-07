<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDtr;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ApiDtrController extends Controller
{
    public function fetchDtr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer',
            'option' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data'], 400);
        }

        $user = User::where('username',$request->username)->first();

        if(!$user){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $api_token = $user->api_token;

       // if (Hash::check($request->token, $api_token)) {

            $option = $request->option;

            if ($option == 'Today') {
                $dtrData = EmployeeDtr::where('id_no', $user->id_no)
                    ->where('date', date('Y-m-d'))
                    ->get();
            } else {
                $year = $request->year;
                $month = $request->month;
                $date = strtotime("$year-$month-01");
                $dtrData = EmployeeDtr::where('id_no', $user->id_no)
                    ->where('date','>=',date('Y-m-01',$date))
                    ->where('date','<=',date('Y-m-t',$date))
                    ->get();
            }

            $responseData = $dtrData->map(function ($dtr) {
                return [
                    'date' => $dtr->date,
                    'time_in_am' => strtotime($dtr->time_in_am) ? date('H:i:s', strtotime($dtr->time_in_am)) : '',
                    'time_out_am' => strtotime($dtr->time_out_am) ? date('H:i:s', strtotime($dtr->time_out_am)) : '',
                    'time_in_pm' => strtotime($dtr->time_in_pm) ? date('H:i:s', strtotime($dtr->time_in_pm)) : '',
                    'time_out_pm' => strtotime($dtr->time_out_pm) ? date('H:i:s', strtotime($dtr->time_out_pm)) : '',
                ];
            });

            return response()->json($responseData, 200);

        // }else{
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
    }
}
