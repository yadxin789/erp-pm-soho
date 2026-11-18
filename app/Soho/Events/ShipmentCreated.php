<?php

namespace App\Soho\Events;

use App\Soho\Models\SystemShipRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SystemShipRecord $shipRecord,
    ) {
    }
}
