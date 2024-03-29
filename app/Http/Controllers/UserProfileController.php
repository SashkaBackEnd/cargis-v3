<?php

namespace App\Http\Controllers;


use App\Enums\StatusEnum;
use App\Http\Requests\UserProfile\AvatarRequest;
use App\Http\Requests\UserProfile\UserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiJsonResponse;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{

    public function index(Request $request)
    {
        return new ApiJsonResponse(
            data: new UserResource($request->user())
        );
    }

    public function addToFavorite(Request $request)
    {   $user = User::where('id',$request->user_id)->first();
        Favorite::firstOrCreate(["user_id"=>$request->user_id, "good_id"=>$request->good_id],["user_id"=>$request->user_id, "good_id"=>$request->good_id]);
        return new ApiJsonResponse(data: new UserResource($user));
    }
    public function store(UserProfileRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        $userProfile = $user->userProfile()
            ->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

        return new ApiJsonResponse(
            200,
            StatusEnum::OK,
            __("user-profile.created"),
            new UserProfileResource($userProfile)
        );
    }


    public function storeAvatar(AvatarRequest $request)
    {
        $user        = $request->user();
        $userProfile = $user->userProfile()->firstOrFail();
    
        if (!is_null($user->userProfile->avatar)) {
            Storage::delete($user->userProfile->avatar);
        }

        $user->userProfile()
            ->update(
                [
                    'avatar' => $request->file('avatar')->store('avatars', 'public')
                ]
            );

        $userProfile = $user->userProfile()->first();

        return new ApiJsonResponse(
            200,
            StatusEnum::OK,
            __("user-profile.created"),
            new UserProfileResource($userProfile)
        );
    }

    public function getCleintIp(){
        return response()->json($_SERVER['REMOTE_ADDR']);
    }
}