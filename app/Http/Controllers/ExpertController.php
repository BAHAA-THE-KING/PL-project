<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class ExpertController extends Controller
{

    public function create()
    {
        //validate request
        try {
            $information = request()->validate([
                'specialty_id' => ['required', 'exists:specialties,id'],
                'price' => ['required', 'numeric', 'between:1,10000'],
                'description' => ['min:5', 'max:300'],
                'address' => ['min:1', 'max:50'],
                'specialization' => ['max:20']
            ]);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }

        //add additional information
        $information['user_id'] = auth()->user()->id;

        //if the passed specialization is null, then make it '' (empty string)
        if (!isset(request()->specialization)) {
            $information['specialization'] = 'general';
        }

        //validating that the new specialty doesn't already exist...
        $alreadyExists = Expert::where('user_id', auth()->user()->id)->where('specialty_id', request()->specialty_id)->where('specialization', $information['specialization'])->first();

        if (isset($alreadyExists)) {
            return response()->json(['msg' => 'you already have this specialty.'], 400);
        }

        //create expert
        Expert::create($information);

        //success message
        $specialtyName = Specialty::find($information['specialty_id'])->specialtyName;

        return response()->json([
            'msg' => "you became $specialtyName expert successfully"
        ], 200);
    }
    public function update(Request $request, $expId)
    {
        try {
            $information = $request->validate([
                'price' => ['numeric', 'between:1,10000'],
                'description' => ['min:5', 'max:300'],
                'address' => ['min:1', 'max:50'],
            ]);
            $userId = auth()->user()->id;
            $expert = Expert::findOrFail($expId);
            if ($userId != $expert->user_id)
                return response()->json([
                    'msg' => "only account's owner can edit it's information"
                ], 422);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        } catch (ModelNotFoundException $e) {
            return response()->json(['msg' => 'speciality not found'], 404);
        }
        $expert->update($information);
        return response()->json([
            'msg' => 'success',
            'speciality' => $expert
        ]);
    }
}
