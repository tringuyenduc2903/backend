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
        protected Employee $employee,
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
                    trans('Account was successfully created by Employee **:name** at **:time**.', [
                        'name' => $this->employee->name,
                        'time' => Carbon::now()->isoFormat(config('backpack.ui.default_datetime_format')),
                    ]),
                    '---',
                    '## '.trans('Please review the information and access your account for the first time:'),
                    sprintf('**%s**: %s', trans('Name'), $this->customer->name),
                    sprintf('**%s**: %s', trans('Email'), $this->customer->email),
                    sprintf('**%s**: %s', trans('Phone number'), $this->customer->phone_number),
                    sprintf('**%s**: %s', trans('Birthday'), $this->customer->birthday),
                    sprintf('**%s**: %s', trans('Gender'), $this->customer->gender_preview),
                    sprintf('**%s**: %s', trans('Password'), $this->password),
                ],
                'outroLines' => [
                    trans('The password is randomly generated and can only be viewed by you via email.'),
                    trans('For security reasons, please change your password after a successful login!'),
                ],
            ],
        );
    }
}
