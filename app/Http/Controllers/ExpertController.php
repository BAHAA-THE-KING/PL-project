<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class ExpertController extends Controller
{
    public function update(Request $request,$expId){
        try{
            $information=$request->validate([
                'price'=>['numeric','between:1,10000'],
                'description'=>['min:5','max:300'],
                'address'=>['min:1','max:50'],
            ]);
            $userId=auth()->user()->id;
            $expert=Expert::findOrFail($expId);
            if($userId!=$expert->user_id)
            return response()->json([
                'msg'=>"only account's owner can edit it's information"],422);
        }catch(ValidationException $e){
            return response()->json(['msg'=>$e->getMessage()],401);
        }catch(ModelNotFoundException $e){
            return response()->json(['msg'=>'speciality not found'],404);
        }
        $expert->update($information);
        return response()->json([
            'msg'=>'success',
            'speciality'=>$expert
        ]);
    }
}
