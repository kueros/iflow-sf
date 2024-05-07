<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallLog extends Model
{
    protected $table = 'install_logs';
    use SoftDeletes;
    protected $fillable = ['hmac', 'host', 'shop', 'state', 'fapiusr', 'fapiclave', 'code', 'access_token', 'token', 'carrier', 'webhook'];

}
