<?php

namespace App\Pages;

use App;

/**
 * App\Pages\TextPage
 *
 * @property int $id
 * @property string $html
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\TextPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\TextPage whereHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\TextPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\TextPage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TextPage extends \Arbory\Base\Pages\TextPage
{
    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'html',
    ];
}