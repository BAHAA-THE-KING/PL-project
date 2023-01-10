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
    public function toggleFavorite()
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
        if ($fav){

            $fav->delete();
            return response()->json(
                [
                    "msg" => "Removed",
                    "message" => "This expert was REMOVED from your favorite list successfully"
                ],
                200
            );
        }

        Favorite::create(["user_id" => $user_id, "expert_id" => $expert_id]);

        return response()->json(
            [
                "msg" => "added",
                "message" => "This expert was ADDED to your favorite list successfully."
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
    public static function doesUserLike($userId, $expertId)
    {
        $number = Favorite::where('user_id', $userId)->where('expert_id', $expertId)->get()->count();
        return $number != 0;
    }
}
