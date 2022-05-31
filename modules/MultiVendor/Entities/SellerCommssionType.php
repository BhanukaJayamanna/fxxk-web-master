<?php

namespace Modules\MultiVendor\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SellerCommssionType extends Model
{
    use HasFactory;
    protected $table = "seller_commissions";
    protected $guarded = ["id"];

    public static function boot()
    {
        parent::boot();
        static::created(function ($brand) {
            $brand->created_by = Auth::user()->id ?? null;
        });

        static::updating(function ($brand) {
            $brand->updated_by = Auth::user()->id ?? null;
        });
    }
}
