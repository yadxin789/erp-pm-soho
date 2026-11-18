<?php

namespace App\Soho\Observers;

use App\Soho\Events\ShipmentCreated;
use App\Soho\Models\SystemShipRecord;

class SystemShipRecordObserver
{
    public function created(SystemShipRecord $shipRecord): void
    {
        event(new ShipmentCreated($shipRecord));
    }
}
