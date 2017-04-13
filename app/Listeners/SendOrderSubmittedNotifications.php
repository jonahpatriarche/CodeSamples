<?php

namespace App\Listeners;

use App\Events\OrderSubmitted;
use App\Mail\OrderSubmittedConfirmation;
use App\Mail\OrderSubmittedNotification;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderSubmittedNotifications extends BaseListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Send email notification to staff member in charge of orders
     *
     * @param  OrderSubmitted  $event
     * @return void
     */
    public function handle(OrderSubmitted $event)
    {
        try {
            $staff = User::where('email', env('ORDERS_EMAIL'))
                ->first();
        }
        catch (ModelNotFoundException $e) {
            Log::warning(
                env('ORDERS_EMAIL') . ' is set as the orders staff email, but user was found in the database'
            );

            $staff = $this->getFallbackEmail();
        }

        try {
            $order = $event->order;

            Mail::to($order->user)
                ->send(new OrderSubmittedConfirmation($order));

            Mail::to($staff)
                ->send(new OrderSubmittedNotification($order));
        }
        catch (\Exception $e) {
            $this->logError($e, 'An error occurred while sending new order emails');
        }

    }
}
