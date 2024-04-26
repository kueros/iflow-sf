<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dato extends Model
{
    use SoftDeletes;

    protected $fillable = ['shop', 'fApiUsr', 'fApiClave'];

    public function editUrl()
    {
        return route('datos.edit', ['id' => $this->id]);
    }

    public function deleteUrl()
    {
        return route('datos.destroy', ['id' => $this->id]);
    }
}
