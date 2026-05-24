<?php

namespace App\Jobs;

use App\Mail\ContactUs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendContactUsEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly private ContactUs $contactUs)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('test@gmail.com')->send($this->contactUs);
    }
}
