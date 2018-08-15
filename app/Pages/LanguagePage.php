<?php

namespace App\Pages;

use App;
use Waavi\Translation\Models\Language;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Pages\LanguagePage
 *
 * @property int $id
 * @property int $language_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Waavi\Translation\Models\Language $language
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\LanguagePage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\LanguagePage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\LanguagePage whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Pages\LanguagePage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LanguagePage extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'language_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->language->getAttribute('locale');
    }
}