<?php

namespace App\Soho\Observers;

use App\Soho\Events\OrderStatusChanged;
use App\Soho\Models\PlatformOrder;

class PlatformOrderObserver
{
    public function updated(PlatformOrder $order): void
    {
        if ($order->isDirty('order_status')) {
            event(new OrderStatusChanged(
                order: $order,
                oldStatus: $order->getOriginal('order_status'),
                newStatus: $order->order_status,
            ));
        }
    }
}
