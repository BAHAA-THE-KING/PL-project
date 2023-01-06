<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Time;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $connectedUser = auth()->user();

        $user_id = $connectedUser->id;

        $page = (int)request()->query("page");

        $allres = Reservation::where("user_id", $user_id)->ORwhere("expert_id", $user_id);

        $length = $allres->count();

        $res = $allres->offset($page * 20)->take(20)->get();

        $hasNext = ($length > ($page + 1) * 20);

        return response()->json(
            [
                "message" => "success",
                "data" => [
                    "Reservations" => $res,
                    "hasNext" => $hasNext
                ]
            ],
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();

        try {
            request()->validate([
                "expert_id" => ["required", Rule::exists("Experts", "id")],
                "startTime" => ["required", "date_format:Y-m-d H:i:s"],
                "endTime" => ["required", "date_format:Y-m-d H:i:s", "after:startTime"]
            ]);
        } catch (Exception $e) {
            return response()->json(
                [
                    'message' => "error",
                    'userMessage' => $e->getMessage()
                ],
                401
            );
        }

        $user_id = $user->id;
        $expert_id = request()->expert_id;
        $startTime = request()->startTime;
        $endTime = request()->endTime;

        if (Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)) < 30) {
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Too Short"
                ],
                401
            );
        }

        if (Carbon::parse($startTime)->diffInHours(Carbon::parse($endTime)) > 5) {
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Too Long"
                ],
                401
            );
        }


        /*<From Time Controller>*/

        $id = request()->id;
        $day = request()->day;
        $reservations = Reservation::where(
            function ($q) use ($day) {
                return response()->json([
                    "message" => "success",
                    "data" => $q
                        ->whereDate("startTime", $day . " 00:00:00")
                        ->orWhereDate("endTime", $day . ' 00:00:00')
                ], 200);
            }
        )->where(function ($q) use ($user, $id) {
            return response()->json(
                [
                    "message" => "success",
                    "data" => $q
                        ->where("expert_id", $user["id"])
                        ->orWhere("user_id", $user["id"])
                        ->orWhere("expert_id", $id)
                        ->orWhere("user_id", $id)
                ],
                200
            );
        })
            ->orderBy("startTime", "asc")
            ->get()->toArray();
        /**/
        $time = Time::where("expert_id", $user["id"])->where("day", Carbon::createFromFormat("d/m/Y", $day)->format("l"))->first();

        if ($time == null)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Not Available"
                ],
                403
            );
        $time = $time->toArray();

        $avtimes = [];
        if (sizeof($reservations) == 0) {
            $avtimes = [
                [
                    "startTime" => $time["start"],
                    "endTime" => $time["end"]
                ]
            ];
        } else {
            if ($reservations[0]["startTime"] < $day . " 00:00:00") {
                $reservations[0]["startTime"] = $day . " 00:00:00";
            }
            if (last($reservations)["endTime"] > $day . " 23:59:59") {
                $reservations[] = [...array_pop($reservations), "endTime" => $day . " 23:59:59"];
            }

            $reservations = array_map(function ($elm) {
                return response()->json(
                    [
                        "message" => "success",
                        "data" => [
                            "startTime" => substr($elm["startTime"], 11, 8),
                            "endTime" => substr($elm["endTime"], 11, 8)
                        ]
                    ],
                    200
                );
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
        }
        /*</From Time Controller>*/

        $canRes = false;
        $tstartTime = substr($startTime, 11, 8);
        $tendTime = substr($endTime, 11, 8);
        foreach ($avtimes as $key => $value) {
            if ((Carbon::parse($tstartTime)->gte(Carbon::parse($value["startTime"]))) &&
                (Carbon::parse($tendTime)->gte(Carbon::parse($value["startTime"]))) &&
                (Carbon::parse($tstartTime)->lte(Carbon::parse($value["endTime"]))) &&
                (Carbon::parse($tendTime)->lte(Carbon::parse($value["endTime"])))
            ) {
                $canRes = true;
                break;
            }
        }
        if (!$canRes) {
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Wrong Time"
                ],
                403
            );
        }
        $user = User::find($user_id);
        $expertise = Expert::find($expert_id);
        $expert = $expertise->user;
        $price = $expertise->price;

        if ($user->money < $price)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Insufficient Funds"
                ],
                403
            );

        $expert->money = $expert->money + $price;
        $user->money = $user->money - $price;
        $expert->save();
        $user->save();

        Reservation::create(["user_id" => $user_id, "expert_id" => $expert_id, "startTime" => $startTime, "endTime" => $endTime, "rate" => -1]);

        return response()->json(
            [
                "message" => "success",
                "data" => "created"
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $res = Reservation::find($id);

        if (!$res)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Reservation Not Found"
                ],
                404
            );
        return response()->json(
            [
                "message" => "success",
                "data" => $res
            ],
            200
        );
    }
}
