<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Order $order,
        protected Employee $employee,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('The order was successfully created'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-created',
            with: [
                'level' => '',
                'introLines' => [
                    trans('Order Id #**:number** was successfully created by Employee **:name** at **:time**.', [
                        'number' => $this->order->id,
                        'name' => $this->employee->name,
                        'time' => Carbon::now()->isoFormat(config('backpack.ui.default_datetime_format')),
                    ]),
                    trans('Please review the information and access your account to track order status:'),
                    '---',
                    '# '.trans('Order account information:'),
                    sprintf('**%s**: %s', trans('Name'), $this->order->customer->name),
                    sprintf('**%s**: %s', trans('Phone number'), $this->order->customer->phone_number),
                    '---',
                    '# '.trans('Recipient information:'),
                    sprintf('**%s**: %s', trans('Name'), $this->order->address->customer_name),
                    sprintf('**%s**: %s', trans('Phone number'), $this->order->address->customer_phone_number),
                    sprintf('**%s**: %s', trans('Address detail'), $this->order->address->address_preview),
                    sprintf('**%s**: %s', trans('Note'), $this->order->note),
                    sprintf('**%s**: %s', trans('Shipping method'), $this->order->shipping_method_preview),
                    sprintf('**%s**: %s', trans('Payment method'), $this->order->payment_method_preview),
                    sprintf('**%s**: %s', trans('Status'), $this->order->status_preview),
                    '---',
                    '# '.trans('Information about products:'),
                ],
                'titles' => [
                    sprintf(
                        '| %s | %s | %s | %s | %s |',
                        trans('Image'),
                        trans('Name'),
                        trans('Price'),
                        trans('Amount'),
                        trans('Make money'),
                    ),
                    '| --- | :---: | :---: | :---: | ---: |',
                ],
                'options' => get_options($this->order),
                'outroLines' => [],
            ],
        );
    }
}
