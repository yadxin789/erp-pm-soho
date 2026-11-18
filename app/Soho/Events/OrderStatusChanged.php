<?php

namespace App\Soho\Events;

use App\Soho\Models\PlatformOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PlatformOrder $order,
        public string $oldStatus,
        public string $newStatus,
    ) {
    }
}
