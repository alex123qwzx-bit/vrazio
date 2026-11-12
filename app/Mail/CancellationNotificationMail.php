<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderCancellation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $event 'requested' | 'approved' | 'rejected'
     */
    public function __construct(
        public Order $order,
        public OrderCancellation $cancellation,
        public string $event
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'requested' => "Cancellation Request Received — Order #{$this->order->order_number} | VRAZIO",
            'approved' => "Cancellation Approved — Order #{$this->order->order_number} | VRAZIO",
            'rejected' => "Update Regarding Cancellation Request — Order #{$this->order->order_number} | VRAZIO",
            default => "Cancellation Update — Order #{$this->order->order_number} | VRAZIO",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation_notification',
        );
    }
}
