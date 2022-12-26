<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
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

        try {
            request()->validate([
                "expert_id" => ["required", Rule::exists("Experts", "user_id")]
            ]);
        } catch (Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }

        $user_id = $connectedUser->id;
        $expert_id = request()->expert_id;

        if ($user_id == $expert_id)
            return response()->json(["message" => "You Can't Add Yourself To Your Favorite List."], 401);

        $fav = Favorite::where("user_id", $user_id)->where("expert_id", $expert_id)->first();
        if ($fav)
            return response()->json(["message" => "Expert Is Already In Your Favorite List."], 401);

        Favorite::create(["user_id" => $user_id, "expert_id" => $expert_id]);

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
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function show(Favorite $favorite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function edit(Favorite $favorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Favorite $favorite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {

        $connectedUser = auth()->user();

        try {
            request()->validate([
                "expert_id" => ["required", Rule::exists("Experts", "user_id")]
            ]);
        } catch (Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }

        $user_id = $connectedUser->id;
        $expert_id = request()->expert_id;

        if ($user_id == $expert_id)
            return response()->json(["message" => "You Can't Remove Yourself From Your Favorite List."], 401);

        $fav = Favorite::where("user_id", $user_id)->where("expert_id", $expert_id)->first();

        if (!$fav)
            return response()->json(["message" => "The Expert Is Not In Your Favorite List."]);

        $fav->delete();

        return response()->json(["message" => "success"]);
    }
    public static function doesUserLike($userId, $expertId)
    {
        $number = Favorite::where('user_id', $userId)->where('expert_id', $expertId)->get()->count();
        return $number != 0;
    }
}
