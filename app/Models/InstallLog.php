<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallLog extends Model
{
    protected $table = 'install_logs';
    use SoftDeletes;
    protected $fillable = ['shopId', 'token', 'code', 'cuit', 'shop', 'fapiusr', 'fapiclave', 'hmac', 'host', 'state'];

}
