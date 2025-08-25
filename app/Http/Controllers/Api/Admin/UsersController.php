<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\Status;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Mail\AccountCreatedMail;
use App\Models\UserApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class UsersController extends ApiController
{
    public function index(Request $request)
    {


        $status = $request->query('status');
        $search = $request->query('search');


        $query = User::users();

        if ($status) {
            if (!in_array($status, ["active", "suspended", "pending"])) {
                return $this->errorForbidden("Invalid status value");
            }
            $query->where(
                'account_status',
                $status
            );
        }

        if ($search) {
            $query->where('first_name', 'like', '%' . $search . '%')
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }

        $users = $query->get()->map(function ($user) {
            return [
                "id" => $user->id,
                "name" => $user->name,
                "location" => $user->location,
                "address" => $user->address,
                "listings_count" => $user->properties()->count(),
                "profile_pic_url" => $user->profile_image_url,
                "account_status" => $user->account_status
            ];
        });

        return $this->respondWithSuccess("", $users);
    }

    public function getStats()
    {
        $data = [
            "total_users" => User::users()->where('account_status', Status::ACTIVE)->count(),
            "online_users" => User::online()->count(),

        ];
        return $this->respondWithSuccess("Fetched stats", $data);

    }


    public function show(Request $request, User $user)
    {
        $user->load(['properties', 'application']);
        return $this->respondWithSuccess("", $user);
    }


    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,rejected',
            'reason' => 'required_if:status,rejected'
        ]);
        $application = $user->application;
        if ($application && $request->status != "suspended") {
            $application->status = $request->status;
            if ($request->reason) {
                $application->rejection_reason = $request->reason;
            }
            $application->save();
        }
        $user->update(['account_status' => $request->status]);

        return $this->respondWithSuccess('Updated agent status', $user);
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if(User::whereEmail($request->email)->exists()) {
            return $this->respondWithError('User with this email already exists', 409);
        }

        $user = User::create([
            'email' => $request->email,
            'account_type' => User::TYPE_AGENT,
            'account_status' => Status::ACTIVE,
        ]);

        Mail::to($user->email)->send(new AccountCreatedMail($user));

        return $this->respondWithSuccess('User added successfully', $user);
    }
}