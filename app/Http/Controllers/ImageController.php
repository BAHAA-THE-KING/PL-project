<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function handleImage(){
        $user = auth() -> user();
        if (file_exists($user->image)) 
            file_put_contents($user->image, "");
        $user->image = ImageController::storeImage();
        return response()->json(["message"=>"success"]);
    }
    static public function storeImage(){
        request()->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048'
            ]);
        $imageName = time() . "." . request()->image->extension();
        request()->image->storeAs("images",$imageName);
        return "images".$imageName;
        }
}
