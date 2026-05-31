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
            ->where('registration_reminder_sent', false)
            ->where('created_at', '<=', now()->subDay())
            ->get();

        $this->info("Found {$users->count()} users with incomplete registration.");

        foreach ($users as $user) {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\RegistrationReminderMail());
            $user->registration_reminder_sent = true;
            $user->save();

            $this->info("Sent registration reminder email to: {$user->email}");
        }

        $this->info('Registration reminders processed successfully.');
    }
}
