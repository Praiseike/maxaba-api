<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Models\AgentApplication;
use App\Models\Property;
use App\Models\User;
use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends ApiController
{
    public function uploadAppLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $file = $request->file('logo');

        if ($file) {
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('/'), $filename);

            return $this->respondWithSuccess("Logo updated successfully");
        }

        return $this->respondWithError("No logo file provided");
    }

    public function getStats(){

        $admin = auth("admin")->user();
        $notifications = $admin->notifications;
        
        $grouped = $notifications->groupBy(function ($notification) {
            $createdAt = Carbon::parse($notification->created_at);
        
            if ($createdAt->isToday()) {
                return 'Today';
            } elseif ($createdAt->isYesterday()) {
                return 'Yesterday';
            } elseif ($createdAt->diffInDays() < 7) {
                return $createdAt->format('l');
            } else {
                return $createdAt->format('M j, Y'); 
            }
        });

        $data = [
            "total_agents" => User::agents()->count(),
            "pending_agents" => User::agent()->where('status',Status::PENDING)->count(),
            "total_houses" => Property::count(),
            "pending_houses" => Property::where('status',Status::PENDING)->count(),
            "recent_activities" => $grouped
        ];
        return $this->respondWithSuccess("Fetched stats", $data);
    }
}
