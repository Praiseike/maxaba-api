<?php

namespace App\Http\Controllers\Api\Agents;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AgentApplication;
use App\Models\User;
use App\Notifications\AgentRegisteredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AgentsController extends ApiController
{
    public function becomeAgent(Request $request){
        $request->validate([
            'id_file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,docx|max:2048',
            'proof_of_address_file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,docx|max:2048',
        ]);

        $user = $request->user();

        $idPath = $request->file('id_file')->store('agent_applications/id_files', 'public');
        $proofOfAddressPath = $request->file('proof_of_address_file')->store('agent_applications/proofs_of_address', 'public');
        
        $application = new AgentApplication();

        $application->user_id = $user->id;
        $application->id_path = $idPath;
        $application->proof_of_address_path = $proofOfAddressPath;
        $application->status = 'pending';
        $user->account_type = User::TYPE_AGENT;
        $user->save();
        $application->save();

        Notification::send(Admin::all(), new AgentRegisteredNotification($user));


        return $this->respondWithSuccess("Agent application submitted successfully", [
            'application_id' => $application->id,
            'status' => $application->status,
        ]);

    }

    public function getAgentApplication(Request $request){
        $user = $request->user();
        $application = \App\Models\AgentApplication::where('user_id', $user->id)->first();

        if(!$application){
            return $this->respondWithError("No agent application found");
        }

        return $this->respondWithSuccess("Agent application details", [
            'application' => $application,
        ]);
    }

}



