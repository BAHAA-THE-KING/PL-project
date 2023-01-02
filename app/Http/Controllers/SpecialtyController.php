<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    public function create()
    {
        //validate the request
        try {
            $specialty = request()->validate([
                'specialtyName' => [
                    'required', 'unique:specialties', 'min:5', 'max:30'
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }

        //create specialty
        Specialty::create($specialty);

        return response()->json(['msg' => 'specialty has been added successfuly']);
    }

    public function search($id)
    {
        $word = request()->header('word');
        
        $query = Expert::where('specialty_id',$id)
        ->where(function($query) use($word){
            $query->orWhere('specialization','like','%'.$word.'%')
            ->orWhere('name','like','%'.$word.'%')
            ->orWhere('description','like','%'.$word.'%');
        })
        ->join('users','users.id','experts.user_id')
        ->select('experts.id','user_id','name','image','specialty_id','price','rateSum','rateCount')
        ->orderBy('rateCount','desc')
        ->get();

        foreach($query as $t){

            $t['rating'] = $t['rateCount'] ? round($t['rateSum']/$t['rateCount'],1) : 'no ratings yet';
        }

        return $query->count() ? $query : response()->json(['msg'=>'no results'],404);
    }

    static public function getSpecialtiesList()
    {
        $specialties = Specialty::get();
        return $specialties;
    }
}
