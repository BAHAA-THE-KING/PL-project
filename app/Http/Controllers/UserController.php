<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\FavoriteController;
use App\Models\Favorite;
use App\Models\Reservation;
use App\Models\Specialty;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $specs = SpecialtyController::getSpecialtiesList();

        $favs = Favorite::where("user_id", $user["id"])->take(10)->orderBy("id", "desc")->get();

        $res = Reservation::where("user_id", $user["id"])->take(10)->orderBy("id", "desc")->get();

        return response()->json(["Specialities" => $specs, "Favorites" => $favs, "Reservations" => $res]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $information = request()->validate([
                'name' => ['required', 'min:1', 'max:25'],
                'phone' => ['required', 'unique:users,phone', 'min:7', 'max:15'],
                'password' => ['required', 'min:1', 'max:45']
            ]);
            $information["image"] = ImageController::storeImage(false);
            if (!$information["image"])
                $information["image"] = "none";
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }
        $user = User::create($information);
        $token = $user->createToken('user')->plainTextToken;
        return response()->json(['msg' => 'Success', 'token' => $token], 200);
    }
    public function login()
    {
        //validate the data and make sure that the number exists
        try {
            $information = request()->validate([
                'phone' => ['required', 'exists:users,phone', 'min:7', 'max:15'], 'password' => ['required', 'min:1', 'max:45']
            ]);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }
        $user = User::where('phone', $information['phone'])->get()->first();
        //make sure the pass is ok
        if (!Hash::check($information['password'], $user->password))
            return response()->json(['msg' => 'Wrong password'], 403);
        //nice....now generate a token
        $token = $user->createToken('user')->plainTextToken;
        $json = [
            'msg' => 'Success', 'token' => $token
        ];
        return response()->json($json, 200);
    }
    public function logout()
    {
        //remove user's current token
        request()->user()->currentAccessToken()->delete();
        return response(['msg' => 'Loged out successfully']);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $connectedUser = auth()->user();
        try {
            $json = User::findOrFail($id);
            if ($connectedUser->id == $json['id']) {
                $json->makeVisible(['phone', 'money']);
            }
            $json['isExp'] = $json->expert->where('active',1)->first() != null;
            $json['msg'] = 'success';
            $json['canEdit'] = $connectedUser->id == $id;
            $json['isFav'] = $json['canEdit'] ? false : FavoriteController::doesUserLike($connectedUser->id, $id);
            if($json['isExp'] || $json['canEdit']){
                $json['Expertise'] = $json['canEdit'] ? $json->expert :
                $json->expert->where('active',1);
            }
            $json->makeHidden(['expert']);
            return response()->json($json);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'msg' => 'User not found'
            ], 404);
        }
    }

    public function getFavoriteList()
    {
        $connectedUser = auth()->user();
        $result = User::whereHas('lovedExperts', fn ($query) => $query->where('user_id', $connectedUser->id))
            ->orderBy('created_at')
            ->paginate(10);
        return $result;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $information = $request->validate([
                'name' => ['min:1', 'max:25'],
                'password' => ['min:1', 'max:45'],
                'phone' => 'Prohibited',
                'money' => 'prohibited',
                'image' => 'prohibited'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['msg' => $e->getMessage()], 401);
        }
        $connectedUser = auth()->user()->id;
        $connectedUser = User::find($connectedUser);
        $connectedUser->update($information);
        return response()->json([
            'msg' => 'success',
            'user' => $connectedUser
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
