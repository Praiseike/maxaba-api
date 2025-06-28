<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\Status;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Mail\AccountCreatedMail;
use App\Models\AgentApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class AgentsController extends ApiController
{
    public function index(Request $request)
    {


        $status = $request->query('status');
        $search = $request->query('search');


        $query = User::agents();

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

        $agents = $query->get()->map(function ($agent) {
            return [
                "id" => $agent->id,
                "name" => $agent->name,
                "location" => $agent->location,
                "address" => $agent->address,
                "listings_count" => $agent->properties()->count(),
                "profile_pic_url" => $agent->profile_image_url,
                "account_status" => $agent->account_status
            ];
        });

        return $this->respondWithSuccess("", $agents);
    }

    public function getStats()
    {
        $data = [
            "total_agents" => User::agents()->where('account_status', Status::ACTIVE)->count(),
            "pending_agents" => User::agents()->where('account_status', Status::PENDING)->count(),

        ];
        return $this->respondWithSuccess("Fetched stats", $data);

    }


    public function show(Request $request, User $agent)
    {
        $agent->load(['properties', 'application']);
        return $this->respondWithSuccess("", $agent);
    }


    public function updateStatus(Request $request, User $agent)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,rejected',
            'reason' => 'required_if:status,rejected'
        ]);
        $application = $agent->application;
        if ($application && $request->status != "suspended") {
            $application->status = $request->status;
            if ($request->reason) {
                $application->rejection_reason = $request->reason;
            }
            $application->save();
        }
        $agent->update(['account_status' => $request->status]);

        return $this->respondWithSuccess('Updated agent status', $agent);
    }

    public function addAgent(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if(User::whereEmail($request->email)->exists()) {
            return $this->respondWithError('Agent with this email already exists', 409);
        }

        $agent = User::create([
            'email' => $request->email,
            'account_type' => User::TYPE_AGENT,
            'account_status' => Status::ACTIVE,
        ]);

        Mail::to($agent->email)->send(new AccountCreatedMail($agent));

        return $this->respondWithSuccess('Agent added successfully', $agent);
    }
}