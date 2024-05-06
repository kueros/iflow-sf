<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Shopify extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shopify';
    use SoftDeletes;

    protected $fillable = ['hmac', 'host', 'shop', 'state', 'fapiusr', 'fapiclave', 'code', 'access_token', 'token', 'carrier', 'webhook'];

    public function editUrl()
    {
        return route('shopify.edit', ['id' => $this->id]);
    }

    public function deleteUrl()
    {
        return route('shopify.destroy', ['id' => $this->id]);
    }
}
