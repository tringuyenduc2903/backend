<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\OrderMotorcycle;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class AdminOrderMotorcycleCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected OrderMotorcycle $order_motorcycle,
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
                        'number' => $this->order_motorcycle->id,
                        'name' => $this->employee->name,
                        'time' => Carbon::now()->isoFormat(config('backpack.ui.default_datetime_format')),
                    ]),
                    trans('Please review the information and access your account to track order status:'),
                    '---',
                    '# '.trans('Order account information:'),
                    sprintf('**%s**: %s', trans('Name'), $this->order_motorcycle->customer->name),
                    sprintf('**%s**: %s', trans('Phone number'), $this->order_motorcycle->customer->phone_number),
                    '---',
                    '# '.trans('Recipient information:'),
                    sprintf('**%s**: %s', trans('Name'), $this->order_motorcycle->address->customer_name),
                    sprintf('**%s**: %s', trans('Phone number'), $this->order_motorcycle->address->customer_phone_number),
                    sprintf('**%s**: %s', trans('Address detail'), $this->order_motorcycle->address->address_preview),
                    sprintf('**%s**: %s', trans('Note'), $this->order_motorcycle->note),
                    sprintf('**%s**: %s', trans('Payment method'), $this->order_motorcycle->payment_method_preview),
                    sprintf('**%s**: %s', trans('Status'), $this->order_motorcycle->status_preview),
                    '---',
                    '# '.trans('Information about products:'),
                ],
                'titles' => [
                    sprintf(
                        '| %s | %s | %s | %s | %s | %s |',
                        trans('Image'),
                        trans('Name'),
                        trans('SKU'),
                        trans('Price'),
                        trans('Amount'),
                        trans('Make money'),
                    ),
                    '| --- | :---: | :---: | :---: | :---: | ---: |',
                ],
                'options' => $this->getOption(),
                'outroLines' => [],
            ],
        );
    }

    protected function getOption(): array
    {
        $option = [sprintf(
            '| ![%s](%s) | %s%s | %s%s | %s | %s | %s |',
            $this->order_motorcycle->option->product->name,
            Arr::first(json_decode($this->order_motorcycle->option->images)),
            mb_substr($this->order_motorcycle->option->product->name, 0, 9),
            mb_strlen($this->order_motorcycle->option->product->name) > 7 ? '...' : '',
            mb_substr($this->order_motorcycle->option->sku, 0, 9),
            mb_strlen($this->order_motorcycle->option->sku) > 7 ? '...' : '',
            price($this->order_motorcycle->price),
            $this->order_motorcycle->amount,
            price($this->order_motorcycle->price * $this->order_motorcycle->amount),
        )];

        $appends = array_map(
            fn (object $item): string => sprintf(
                '||||| **%s** | %s |',
                $item->label,
                $item->value,
            ),
            [
                (object) [
                    'label' => trans('Tax'),
                    'value' => price($this->order_motorcycle->tax),
                ],
                (object) [
                    'label' => trans('Handling fee'),
                    'value' => price($this->order_motorcycle->handling_fee),
                ],
                (object) [
                    'label' => trans('Total amount'),
                    'value' => price($this->order_motorcycle->total),
                ],
            ]
        );

        return array_merge($option, $appends);
    }
}
