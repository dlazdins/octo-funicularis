<?php

namespace App\Products;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
    {

    /**
    * @var string
    */
    protected $table = 'item_translations';

    /**
    * @var array
    */
    protected $fillable = [
    'name',
    'description',
    ];

}