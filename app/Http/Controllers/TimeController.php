<?php

namespace App\Http\Controllers;

use App\Models\Time;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function show(Time $time)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function edit(Time $time)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Time $time)
    {
        try{
            $information=$request->validate([
                'start' => 'required|date_format:H:i:s',
                'end' => 'required|date_format:H:i:s|after:start',
            ]);
        }catch(ValidationException $e){
            return response()->json(['msg'=>$e->getMessage()],401);
        }
        if($time->expert_id!=auth()->user()->id){
            return response()->json([
                'msg'=>"only account's owner can edit it's information"
            ],422);
        }
        $time->update($information);
        $reponse=[
            'msg'=>'success',
            'time'=>$time
        ];
        return $reponse;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Time  $time
     * @return \Illuminate\Http\Response
     */
    public function destroy(Time $time)
    {
        //
    }
}
