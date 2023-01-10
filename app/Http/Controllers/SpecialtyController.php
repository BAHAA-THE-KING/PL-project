<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Specialty;
use Illuminate\Validation\ValidationException;

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
            return response()->json(
                [
                    'message' => "error",
                    'userMessage' => $e->getMessage()
                ],
                400
            );
        }

        //create specialty
        Specialty::create($specialty);

        return response()->json(
            [
                'message' => 'success',
                'data' => 'specialty has been added successfuly'
            ],
            200
        );
    }
    public function search($id)
    {
        $data=request("query")??"";
        $query = Expert::when($id>0,fn($query)=>$query
            ->where('specialty_id', $id))
            ->where(function ($query) use ($data) {
                $query->orWhere('specialization', 'like', '%' . $data . '%')
                    ->orWhere('name', 'like', '%' . $data . '%')
                    ->orWhere('description', 'like', '%' . $data . '%');
            })
            ->join('users', 'users.id', 'experts.user_id')
            ->select('experts.id', 'user_id', 'name', 'image', 'specialty_id', 'price', 'rateSum', 'rateCount')
            ->orderBy('rateCount', 'desc')
            ->get();

        foreach ($query as $t) {

            $t['rating'] = $t['rateCount'] ? round($t['rateSum'] / $t['rateCount'], 1) : 'no ratings yet';
        }

        $query = $query->sortByDesc('rating');
        return $query->count() ? response()->json(
            [
                "message" => "success",
                "data" => array_values($query->toArray())
            ],
            200
        )
            : response()->json(
                [
                    'message' => 'error',
                    'userMessage' => 'no results'
                ],
                404
            );
    }

    static public function index()
    {
        $specialties = Specialty::get();
        return response()->json(
            [
                "message" => "success",
                "data" => $specialties
            ],
            200
        );
    }
}
