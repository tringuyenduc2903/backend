<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Customer $customer,
        protected Employee $admin,
        protected string $password
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('Customer account successfully created'),
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
                    trans('Account successfully created by Staff :name at :time.', [
                        'name' => $this->admin->name,
                        'time' => Carbon::now()->isoFormat(config('backpack.ui.default_datetime_format')),
                    ]),
                    trans('Please review the information and access your account for the first time:'),
                    trans(':attribute: :value', [
                        'attribute' => trans('Name'),
                        'value' => $this->customer->name,
                    ]),
                    trans(':attribute: :value', [
                        'attribute' => trans('Email'),
                        'value' => $this->customer->email,
                    ]),
                    trans(':attribute: :value', [
                        'attribute' => trans('Phone number'),
                        'value' => $this->customer->phone_number,
                    ]),
                    trans(':attribute: :value', [
                        'attribute' => trans('Birthday'),
                        'value' => $this->customer->birthday,
                    ]),
                    trans(':attribute: :value', [
                        'attribute' => trans('Gender'),
                        'value' => $this->customer->gender_preview,
                    ]),
                    trans(':attribute: :value', [
                        'attribute' => trans('Password'),
                        'value' => $this->password,
                    ]),
                ],
                'outroLines' => [
                    trans('The password is randomly generated and can only be viewed by you via email.'),
                    trans('For security reasons, please change your password after a successful login!'),
                ],
            ],
        );
    }
}
