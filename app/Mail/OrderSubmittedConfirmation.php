<?php

namespace App\Mail;

use App\Order;
use Illuminate\Support\Facades\Log;

class OrderSubmittedConfirmation extends Mailable
{
    /**
     * @var \App\Order
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @param \App\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        try {
            $view = $this->view('mails.orders.submitted')
                ->text('mails.orders.submitted_text')
                ->subject('Please confirm details of order #' . $this->order->id);

            return $view;
        }
        catch (\Exception $e) {
            Log::error(
                'Error occurred while sending order confirmation email to a customer!
                        Message: ' . $e->getMessage() . '
                        File: ' . $e->getFile() . ' (' . $e->getLine() . ')
                        Trace:
                            ' . $e->getTraceAsString()
            );
        }
    }

}
