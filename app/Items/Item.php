<?php

namespace App\items;

use App\Support\NodeUrl;
use Arbory\Base\Files\ArboryImage;
use Arbory\Base\Support\Translate\Translatable;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use Translatable;

    protected static $slugName = 'slug';

    /**
     * @param string $slug
     * @return static
     */
    public static function getBySlug($slug)
    {
        /** @var Item $model */
        $model = new static;
        $table = $model->getTranslationsTable();

        $item = static::translated()
            ->join($table, $table. '.' . $model->getRelationKey(), $model->table . '.id')
            ->where([
                [$table . '.' . static::$slugName, $slug],
                [$table . '.locale', app()->getLocale()]
            ])
            ->select($model->table . '.*');

        return $item->firstOrFail();
    }

    /**
     * @var string
     */
    protected $table = 'items';

    /**
     * @var array
     */
    protected $fillable = [
        'active',
        'price',
        'order',
        'image_id',
        'list_image_id',
    ];

    /**
     * @var array
     */
    protected $translatedAttributes = [
        'name',
        'description',
        'slug'
    ];

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo( ArboryImage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function list_image()
    {
        return $this->belongsTo( ArboryImage::class);
    }

    /**
     * @param $price
     */
    public function setPriceAttribute($price)
    {
        $this->attributes['price'] = $price * 100;
    }

    /**
     * @param $price
     * @return string
     */
    public function getPriceAttribute($price)
    {
        return $price / 100;
    }

    /**
     * @return string
     */
    public function getFullPrice()
    {
        return '€ ' . number_format($this->price, 2, '.', '');
    }

//    /**
//     * @return string
//     */
//    public function url()
//    {
//        return app(NodeUrl::class)->get(ReservationPage::class) . '/' . $this->slug;
//    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRelated()
    {
        return self::where(['active' => 1, ['amount', '>', 0]])
            ->orderByRaw('type_id = ' . $this->type_id . ' desc')
            ->orderBy('order')
            ->limit(4)
            ->get();
    }

//    /**
//     * Get the identifier of the Buyable item.
//     *
//     * @param null $options
//     * @return int|string
//     */
//    public function getBuyableIdentifier($options = null)
//    {
//        return $this->id;
//    }
//
//    /**
//     * Get the description or title of the Buyable item.
//     *
//     * @param null $options
//     * @return string
//     */
//    public function getBuyableDescription($options = null)
//    {
//        return $this->name;
//    }
//
//    /**
//     * Get the price of the Buyable item.
//     *
//     * @param null $options
//     * @return float
//     */
//    public function getBuyablePrice($options = null)
//    {
//        return $this->price;
//    }
//
//    /**
//     * @return string|null
//     */
//    public function getMetaTitle()
//    {
//        return $this->name;
//    }
//
//    /**
//     * @return string|null
//     */
//    public function getMetaDescription()
//    {
//        return $this->description;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMetaImage()
//    {
//        return $this->image;
//    }
}
