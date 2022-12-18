<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $connectedUser=auth()->user()->id;
        $data = ["user_id"=>$connectedUser,"expert_id"=>request()["expert_id"]];
        $favorite=Favorite::create($data);
        return response()
        ->json([
            "message"=>"success"
        ]);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $connectedUser = auth()->user();
        $favorite=Favorite::where("user_id",$connectedUser->id)->where("expert_id",request("expert_id"))->first();
        Favorite::destroy($favorite->id);
        return response()->json([
            "message"=>"success"
        ]);
    }
    public static function doesUserLike($userId,$expertId)
    {
        $number=Favorite::where('user',$userId)->where('expert',$expertId)->get()->count();
        return $number!=0;
    }
}
