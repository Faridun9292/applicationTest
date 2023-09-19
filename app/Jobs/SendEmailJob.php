<?php

namespace App\Jobs;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Application $application)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::send('email', [
            'comment' => $this->application->comment,
            'name' => $this->application->name,
            'date' => $this->application->created_at->format('d.m.Y H:i:s')
        ],  function ($message) {
            $message->to($this->application->email)
                ->subject('title');
        });
    }
}
