<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $admin;
    public $password;
    public $isPasswordReset;

    /**
     * Create a new message instance.
     */
    public function __construct(Admin $admin, string $password, bool $isPasswordReset = false)
    {
        $this->admin = $admin;
        $this->password = $password;
        $this->isPasswordReset = $isPasswordReset;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isPasswordReset 
            ? 'Your Admin Account Password Has Been Reset'
            : 'Welcome to Admin Dashboard - Your Account Details';

        return $this->subject($subject)
                   ->view('mail.admin-welcome')
                   ->with([
                       'admin' => $this->admin,
                       'password' => $this->password,
                       'isPasswordReset' => $this->isPasswordReset,
                       'loginUrl' => config('app.admin_url') . '/ogin',
                       'appName' => config('app.name', 'Admin Dashboard')
                   ]);
    }
}