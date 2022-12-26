<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Message;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use function PHPSTORM_META\map;

class MessageController extends Controller
{
    public function getChat($id){//reservation id
        $connectedUserId=auth()->user()->id;
        $reservation=Reservation::find($id);
        $expertsUserId=Expert::find($reservation->expert_id)->user;
        $expertsUserId=$expertsUserId->id;
        if($reservation->user_id !=$connectedUserId 
        && $expertsUserId !=$connectedUserId )
        return response()->json(['msg'=>'you dont belong to this chat'],422);
        $messages=Message::where('reservation_id',$id)->orderByDesc('created_at')->get();
        foreach ($messages as $message) {
            $message->setAttribute('isMine',$connectedUserId==$message->fromUser);
        }
        $messages->makeHidden(['fromUser','toUser','updated_at','id','reservation_id']);
        return $messages;
    }
    public function prepareConnection(){
        try{
            $resId=request()->validate([
                'reservation_Id'=>'required|numeric|exists:reservations,id'
            ]);
            $resId=$resId['reservation_Id'];
        }catch(ValidationException $e){
        return response()->json(['msg'=>$e->getMessage()],401);
        }
        //get the connected user
        $connectedUserId=auth()->user()->id;
        //check to see if already connected 
        if(Cache::has($this->getUsersReservationKey($connectedUserId)))
        return ['msg'=>'already connected!'];
        //get the reservation object
        $reservation=Reservation::find($resId);
        //get the user id for the expert of the reservation
        $expertsUserId=Expert::find($reservation->expert_id)->user;
        $expertsUserId=$expertsUserId->id;
        //check to see if the connected user isn't one of the users in the reservation 
        //.. if not end the request 
        
        if($reservation->user_id !=$connectedUserId 
        && $expertsUserId !=$connectedUserId )
        return response()->json(['msg'=>'you dont belong to this chat'],422);
        // user is good to go
        // now chache the "connectionData" for this user
        // to connect him to the chat and give him the data he needs 
        //but before that parse the end date to timestamp
        $expirationDate=new Carbon($reservation->endTime);
        $connectionData=[
            'reservation'=>$reservation,
            'isExpert'=>$connectedUserId==$expertsUserId,
            "expert's userId"=>$expertsUserId
        ];
        if(now()->addHours(3)->gte($expirationDate))
        return response()->json(['msg'=>'reservation is done .. you cant connect to the chat'],422);
        Cache::put($this->getUsersReservationKey($connectedUserId)
        ,$connectionData
        ,$expirationDate->subHours(3));
        //now user is connnected to the session until it ends :)
        return ['msg'=>'connected successfully'];
    }
    public function liveMessages(Request $request){
        //return auth()->user()->id;
        try{
            $messages=request()->validate([
                'msgs'=>'required'
            ]);
        }catch(ValidationException $e){
            return response()->json(['msg'=>$e->getMessage()],401);            
        }
        //connected user id
        $connectedUserId=auth()->user()->id;
        // see if the user has a valid reservation in the cache 
        //-> if he has it then he is connected to some chat else he is not
        $connectionData=Cache::get($this->getUsersReservationKey($connectedUserId),-100);
        if($connectionData==-100){
            return response()->json([
                'msg'=>'you are not connected to any reservation chat ... try calling prepareConnection'
            ],404);
        }
        // now the user has a valid chat ... add his new msgs to db 
        $reservation=$connectionData['reservation'];
        $messages=$this->insertMessagesToDB($messages,$connectionData,$connectedUserId);
        // now cache the messages for the other user to recieve
        $cacheKey=$this->getUsersStorageKey($connectedUserId,$reservation->id);
        $this->addMessagesToCache($cacheKey,$messages,$reservation);
        // now recieve the other user's messages and return them in a response :)
        $toUser=!$connectionData['isExpert']?
        $connectionData["expert's userId"]:$connectionData['reservation']->user_id;
        
        $cacheKey=$this->getUsersStorageKey($toUser,$reservation->id);
        $responseData=Cache::get($cacheKey,[]);
        Cache::forget($cacheKey);
        return $responseData;
    }
    private function addMessagesToCache($cacheKey,$messages,$reservation){
        $data=Cache::get($cacheKey,[]);
        $messages=array_map(fn($msg)=>[
            'created_at'=>$msg['created_at'],
            'message'=>$msg['message']
        ],$messages);
        $data=array_merge($data,$messages);
        $expirationDate=new Carbon($reservation->endTime);
        Cache::put($cacheKey,$data,$expirationDate->subHours(3));
    }
    private function insertMessagesToDB($messages,$connectionData,$connectedUserId){
        $messages=json_decode($messages['msgs'],true);
        if(count($messages)==0)return [];
        $fromUser=$connectionData['isExpert']?
        $connectionData["expert's userId"]:$connectedUserId;
        $toUser=!$connectionData['isExpert']?
        $connectionData["expert's userId"]:$connectionData['reservation']->user_id;
        $messageInfo=[
            'fromUser'=>$fromUser,
            'toUser'=>$toUser,
            'reservation_id'=>$connectionData['reservation']->id,
            'created_at'=>now()->addHours(3),
            'updated_at'=>now()->addHours(3)
        ];
        $inserts=array_map(function($msg)use($messageInfo){
            $messageInfo['message']=$msg;
            return $messageInfo;
        },$messages);
        Message::insert($inserts);
        return $inserts;
    }
    public function getUsersReservationKey($userId){
        return "user($userId)"."reservation";
    }
    public function getUsersStorageKey($userId,$reservationId){
        return "user($userId)&&reservation($reservationId)";
    }
}
