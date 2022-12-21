<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    public function create()
    {
        //validate the request
        try{
            $specialty = request()->validate([
                'specialtyName' => [
                    'required','unique:specialties', 'min:5', 'max:30']
            ]);
        }
        catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
        
        //create specialty
        Specialty::create($specialty);

        return response()->json(['msg' => 'specialty has been added successfuly']);
    }

    public function getSpecialtiesList()
    {
        auth();
        $specialties = Specialty::get();
        return $specialties;
    }
}
