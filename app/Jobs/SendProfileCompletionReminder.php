<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\RegistrationReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendProfileCompletionReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Double check they still haven't completed their profile before sending
        if (!$this->user->has_profile) {
            Mail::to($this->user->email)->send(new RegistrationReminderMail());
            
            // Just for legacy record keeping
            if (!$this->user->registration_reminder_sent) {
                $this->user->registration_reminder_sent = true;
                $this->user->save();
            }
        }
    }
}
