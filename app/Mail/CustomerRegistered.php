<?php

namespace App\Mail;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Customer $customer) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('Successfully registered customer account'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'notifications::email',
            with: [
                'level' => '',
                'introLines' => [
                    trans('The account was successfully registered at **:time**.', [
                        'time' => Carbon::now()->isoFormat(config('backpack.ui.default_datetime_format')),
                    ]),
                    '---',
                    '# '.trans('Registration information:'),
                    sprintf('**%s**: %s', trans('Name'), $this->customer->name),
                    sprintf('**%s**: %s', trans('Email'), $this->customer->email),
                ],
                'outroLines' => [],
            ],
        );
    }
}
