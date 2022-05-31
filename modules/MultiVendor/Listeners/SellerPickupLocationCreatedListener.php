<?php

namespace Modules\MultiVendor\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\MultiVendor\Events\SellerPickupLocationCreated;
use Modules\Shipping\Entities\PickupLocation;

class SellerPickupLocationCreatedListener
{

    public function __construct()
    {
        //
    }


    public function handle(SellerPickupLocationCreated $event)
    {
        $location = PickupLocation::where('created_by',1)->first();
        $newLocation = $location->replicate();
        $newLocation->created_by = $event->user_id;
        $newLocation->is_set = 1;
        $newLocation->is_default = 1;
        $newLocation->status = 1;
        $newLocation->save();
        return $newLocation;
    }
}
