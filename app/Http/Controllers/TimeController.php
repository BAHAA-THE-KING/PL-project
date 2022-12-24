<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Time;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $connectedUser = auth()->user();

        if (!Expert::where("user_id", $connectedUser->id)->first())
            return response()->json(["message" => "You Are Not An Expert"], 403);

        try {
            request()->validate([
                "times" => ["required", "array", "min:1", "max:7"],
                "times.*.day" => ["required", "distinct", Rule::in(["SAT", "SUN", "MON", "TUE", "WED", "THI", "FRI"])],
                "times.*.start" => ["required", "date_format:H:i", "between:0,23"],
                "times.*.end" => ["required", "date_format:H:i", "after:start", "between:0,23"]
            ]);
        } catch (Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }

        $user_id = $connectedUser->id;
        $idays = request()->times; //i=input
        $cdays = Time::where("expert_id", $user_id)->get(); //c=current
        $odays = ["SAT", "SUN", "MON", "TUE", "WED", "THI", "FRI"];

        foreach ($odays as $key => $day) {
            $ctime = $cdays->where("day", $day)->first();
            $itimeIndex = array_search($day, array_column($idays, "day"));

            $itime = ($itimeIndex !== false) ? $idays[$itimeIndex] : null;

            if (isset($itime)) { //input
                if (isset($ctime)) { //exists
                    //Will Edit
                    $ctime->update(["start" => $itime["start"], "end" => $itime["end"]]);
                } else { //not exists
                    //Will Create
                    Time::create(["expert_id" => $user_id, "day" => $itime["day"], "start" => $itime["start"], "end" => $itime["end"]]);
                }
            } else { //no input
                if (isset($ctime)) { //exists
                    //Will Delete
                    $ctime->delete();
                } else { //not exists
                    //Do Nothing
                }
            }
        }
        return response()->json(["message" => "success"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function show(Time $time)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function edit(Time $time)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Time $time)
    {
        try {
            $information = $request->validate([
                'start' => 'required|date_format:H:i:s',
                'end' => 'required|date_format:H:i:s|after:start',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }
        if ($time->expert_id != auth()->user()->id) {
            return response()->json([
                'msg' => "only account's owner can edit it's information"
            ], 422);
        }
        $time->update($information);
        $reponse = [
            'msg' => 'success',
            'time' => $time
        ];
        return $reponse;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function destroy(Time $time)
    {
        //
    }
}
