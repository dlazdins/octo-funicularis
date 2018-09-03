<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }
}
