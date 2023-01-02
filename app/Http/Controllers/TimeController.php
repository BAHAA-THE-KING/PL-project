<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Reservation;
use App\Models\Time;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = auth()->user();
        $requset = request()->query();
        $requset["id"] = $id;
        $rules = [
            "id" => ["required", Rule::exists("experts", "user_id")],
            "day" => ["required", "date_format:Y-m-d", "after:" . Date::yesterday()->toDateTimeString()]
        ];
        $validate = Validator::make($requset, $rules);
        if ($validate->fails()) {
            return response()->json(["message" => $validate->messages()], 401);
        }
        $id = request()->id;
        $day = request()->day;
        $reservations = Reservation::where(
            function ($q) use ($day) {
                return $q
                    ->whereDate("startTime", $day . " 00:00:00")
                    ->orWhereDate("endTime", $day . ' 00:00:00');
            }
        )->where(function ($q) use ($user, $id) {
            return $q
                ->where("expert_id", $user["id"])
                ->orWhere("user_id", $user["id"])
                ->orWhere("expert_id", $id)
                ->orWhere("user_id", $id);
        })
            ->orderBy("startTime", "asc")
            ->get()->toArray();
        /**/
        $time = Time::where("expert_id", $user["id"])->where("day", "MON")->first();

        if ($time == null)
            return response()->json(["message" => "Not Available"]);
        $time = $time->toArray();
        if (sizeof($reservations) == 0)
            return response()->json(array_values([
                [
                    "startTime" => $time["start"],
                    "endTime" => $time["end"]
                ]
            ]));

        if ($reservations[0]["startTime"] < $day . " 00:00:00") {
            $reservations[0]["startTime"] = $day . " 00:00:00";
        }
        if (last($reservations)["endTime"] > $day . " 23:59:59") {
            $reservations[] = [...array_pop($reservations), "endTime" => $day . " 23:59:59"];
        }

        $reservations = array_map(function ($elm) {
            return [
                "startTime" => substr($elm["startTime"], 11, 8),
                "endTime" => substr($elm["endTime"], 11, 8),
            ];
        }, $reservations);

        $reservations = collect($reservations)->sortBy("startTime")->reverse()->toArray();

        foreach ($reservations as $key => $obj) {
            if ($obj["startTime"] <= $time["start"] && $obj["endTime"] >= $time["start"]) {
                $reservations[$key] = [...$obj, "startTime" => $time["start"]];
                break;
            }
        }

        $reservations = collect($reservations)->sortBy("startTime")->toArray();
        foreach ($reservations as $key => $obj) {
            if ($obj["startTime"] <= $time["end"] && $obj["endTime"] >= $time["end"]) {
                $reservations[$key] = [...$obj, "endTime" => $time["end"]];
                break;
            }
        }

        $busytimes = array_filter($reservations, function ($elm) use ($time) {
            return $elm["startTime"] >= $time["start"] && $elm["endTime"] <= $time["end"];
        });
        $avtimes = [[
            "startTime" => $time["start"],
            "endTime" => $busytimes[0]["startTime"]
        ]];
        for ($i = 1; $i < sizeof($busytimes); $i++) {
            $avtimes[] = [
                "startTime" => $busytimes[$i - 1]["endTime"],
                "endTime" => $busytimes[$i]["startTime"]
            ];
        }
        $avtimes[] = [
            "startTime" => last($busytimes)["endTime"],
            "endTime" => $time["end"]
        ];

        $avtimes = array_filter($avtimes, function ($elm) {
            return Carbon::parse($elm["startTime"])->diffInMinutes(Carbon::parse($elm["endTime"])) >= 30;
        });
        $avtimes = array_values($avtimes);

        //$busytimes = [[
        //    "startTime" => $time["start"],
        //    "endTime" => $avtimes[0]["startTime"]
        //]];
        //for ($i = 1; $i < sizeof($avtimes); $i++) {
        //    $busytimes[] = [
        //        "startTime" => $avtimes[$i - 1]["endTime"],
        //        "endTime" => $avtimes[$i]["startTime"]
        //    ];
        //}
        //$busytimes[] = [
        //    "startTime" => last($avtimes)["endTime"],
        //    "endTime" => $time["end"]
        //];
        //$busytimes = array_filter($busytimes, function ($elm) {
        //    return Carbon::parse($elm["startTime"])->diffInMinutes(Carbon::parse($elm["endTime"])) != 0;
        //});
        //$busytimes = array_values($busytimes);

        $res = $avtimes;
        return response()->json(array_values($res));
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
