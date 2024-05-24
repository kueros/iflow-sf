<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarrierService extends Model
{
    protected $table = 'carrier_services';
    use SoftDeletes;
    protected $fillable = ['carrierServiceId', 'shopId', 'callbackUrl', 'nombre', 'tipo', 'state'];
}
