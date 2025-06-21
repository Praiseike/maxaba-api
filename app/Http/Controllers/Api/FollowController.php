<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
class FollowController extends ApiController
{
    public function follow(User $user, Request $request)
    {
        $request->user()->follow($user);
        return $this->respondWithSuccess( 'Followed successfully.');
    }

    public function unfollow(User $user, Request $request)
    {
        $request->user()->unfollow($user);
        return $this->respondWithSuccess('Unfollowed successfully.');
    }

    public function followers(User $user)
    {
        return $this->respondWithSuccess("Fetched followers",$user->followers);
    }

    public function following(User $user)
    {
        return $this->respondWithSuccess("Fetched following",$user->following);
    }
}
