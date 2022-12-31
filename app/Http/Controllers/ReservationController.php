<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Reservation;
use App\Models\User;
use DateTime;
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

        $allres=Reservation::where("user_id", $user_id)->ORwhere("expert_id", $user_id);

        $length = $allres->count();

        $res = $allres->offset($page * 20)->take(20)->get();

        $hasNext = ($length > ($page + 1) * 20);

        return response()->json(["Reservations" => $res, "hasNext" => $hasNext]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $connectedUser = auth()->user();

        try {
            request()->validate([
                "expert_id" => ["required", Rule::exists("Experts", "id")],
                "startTime" => ["required", "date_format:Y-m-d H:i:s"],
                "endTime" => ["required", "date_format:Y-m-d H:i:s","after:startTime"]
            ]);
        } catch (Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }

        $user_id = $connectedUser->id;
        $expert_id = request()->expert_id;
        $startTime = request()->startTime;
        $endTime = request()->endTime;

        //dd(DateTime::createFromFormat("Y-m-d H:i:s", $startTime)->diff(DateTime::createFromFormat("Y-m-d H:i:s", $endTime)));

        $user = User::find($user_id);
        $expertise = Expert::find($expert_id);
        $expert = $expertise->user;
        $price = $expertise->price;

        if ($user->money < $price)
            return response()->json(["message" => "Insufficient Funds"], 403);

        $expert->money = $expert->money + $price;
        $user->money = $user->money - $price;
        $expert->save();
        $user->save();

        Reservation::create(["user_id" => $user_id, "expert_id" => $expert_id, "startTime" => $startTime, "endTime" => $endTime, "rate" => -1]);

        return response()->json(["message" => "created"], 201);
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
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $res = Reservation::find($id);

        if (!$res)
            return response()->json(["message" => "Reservation Not Found"], 404);
        return response()->json(["message" => "Reservation Found", "Reservation" => $res]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
