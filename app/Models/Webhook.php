<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    protected $table = 'webhooks';
    use SoftDeletes;
    protected $fillable = ['webhookId', 'shopId', 'url', 'tipo', 'state'];
}
