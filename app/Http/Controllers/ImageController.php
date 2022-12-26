<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Env;

class ImageController extends Controller
{
    public function index($name)
    {
        try {
            return response()->file(public_path('..\\storage\\app\\images\\' . $name));
        } catch (Exception $e) {
            dd($e);
        }
    }
    public function handleImage()
    {
        $user = auth()->user();
        if (file_exists($user->image))
            file_put_contents($user->image, "");
        try {
            $user->image = ImageController::storeImage(true);
            $user->save();
        } catch (Exception $e) {
            return response()->json(["message" => $e->getMessage()], 401);
        }
        return response()->json(["message" => "success"]);
    }
    static public function storeImage($isReq)
    {
        try {
            request()->validate(['image' => 'required']);
        } catch (Exception $e) {
            if (!$isReq)
                return null;
            throw $e;
        }
        try {
            request()->validate([
                'image' => 'image|mimes:png,jpg,jpeg|max:512'
            ]);
        } catch (Exception $e) {
            throw $e;
        }

        $imageName = time() . "." . request()->image->extension();
        request()->image->storeAs("images", $imageName);
        return "image/" . $imageName;
    }
}
