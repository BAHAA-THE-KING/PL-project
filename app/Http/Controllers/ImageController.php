<?php

namespace App\Http\Controllers;

use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function handleImage()
    {
        $user = auth()->user();
        if (file_exists($user->image))
            file_put_contents($user->image, "");
        $user->image = ImageController::storeImage(true);
        return response()->json(["message" => "success"]);
    }
    static public function storeImage($isReq)
    {
        try {
            request()->validate(['aimage' => 'required']);
        } catch (Exception $e) {
            if (!$isReq) return;
            throw $e;
        }

        request()->validate([
            'image' => 'image|mimes:png,jpg,jpeg|max:512'
        ]);

        $imageName = time() . "." . request()->image->extension();
        request()->image->storeAs("images", $imageName);
        return "images" . $imageName;
    }
}
