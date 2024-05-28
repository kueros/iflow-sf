<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'configs';

    protected $fillable = [
            'urlroot',
            'cli_id',
            'cli_pass',
            're_dir_url',
            'fi_logs',
            'scope',
            'callback_url_carrier',
            'webhook_address_orders_create',
            'webhook_address_orders_paid',
            'webhook_address_orders_cancelled',
    ];
}
