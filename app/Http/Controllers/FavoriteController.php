<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Exception;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
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
            return response()->json(
                [
                    'message' => "error",
                    "userMessage" => $e->getMessage()
                ],
                401
            );
        }

        $user_id = $connectedUser->id;
        $expert_id = request()->expert_id;

        if ($user_id == $expert_id)
            return response()->json(
                [
                    "message" => "success",
                    "userMessage" => "You Can't Add Yourself To Your Favorite List."
                ],
                401
            );

        $fav = Favorite::where("user_id", $user_id)->where("expert_id", $expert_id)->first();
        if ($fav)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "Expert Is Already In Your Favorite List."
                ],
                401
            );

        Favorite::create(["user_id" => $user_id, "expert_id" => $expert_id]);

        return response()->json(
            [
                "message" => "success"
            ],
            200
        );
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
            return response()->json(
                [
                    'message' => "error",
                    'userMessage' => $e->getMessage()
                ],
                401
            );
        }

        $user_id = $connectedUser->id;
        $expert_id = request()->expert_id;

        if ($user_id == $expert_id)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "You Can't Remove Yourself From Your Favorite List."
                ],
                401
            );

        $fav = Favorite::where("user_id", $user_id)->where("expert_id", $expert_id)->first();

        if (!$fav)
            return response()->json(
                [
                    "message" => "error",
                    "userMessage" => "The Expert Is Not In Your Favorite List."
                ],
                401
            );

        $fav->delete();

        return response()->json(
            [
                "message" => "success"
            ],
            200
        );
    }

    public static function doesUserLike($userId, $expertId)
    {
        $number = Favorite::where('user_id', $userId)->where('expert_id', $expertId)->get()->count();
        return $number != 0;
    }
}
