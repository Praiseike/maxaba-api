<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendRegistrationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-registration-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to users who registered but did not complete their profile registration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::where(function ($query) {
                $query->whereNull('first_name')
                      ->orWhereNull('last_name')
                      ->orWhere('first_name', '')
                      ->orWhere('last_name', '');
            })
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $days = $user->created_at->startOfDay()->diffInDays(now()->startOfDay());
            
            // Send reminder every 3 days after registration
            if ($days > 0 && $days % 3 === 0) {
                \App\Jobs\SendProfileCompletionReminder::dispatch($user);
                $count++;
            }
        }

        $this->info("Dispatched profile completion reminders for {$count} users.");
    }
}
