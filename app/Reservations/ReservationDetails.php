<?php


namespace App\Reservations;

use Arbory\Merchant\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ReservationDetails extends Model
{
    use Notifiable;

    protected $table = 'reservation_details';

    /**
     * @var array
     */
    protected $fillable = [
        'person_type',
        'company_name',
        'company_code',
        'company_country',
        'company_city',
        'company_postal_code',
        'company_street',
        'company_bank',
        'company_account',
        'company_person',
        'first_name',
        'last_name',
        'phone',
        'email',
        'comments',
        'agree_to_rules',
        'subscribe_to_news',
    ];

    /**
     * @return bool
     */
    public function isLegal()
    {
        return $this->person_type === 'legal';
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->person_type === 'private';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function reservation()
    {
        return $this->morphOne(Reservation::class, 'owner');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function shipping()
    {
        return $this->morphTo('shipping');
    }

    /**
     * @return mixed|string
     */
    public function getBuyersName()
    {
        return $this->isLegal() ? $this->company_name : $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return mixed|string
     */
    public function getNameAttribute()
    {
        return $this->getBuyersName();
    }

    /**
     * @return null|string
     */
    public function getCodeAttribute()
    {
        return $this->company_code;
    }

    /**
     * @param null $objectClass
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    public function getOrderUnitLabel($objectClass = NULL)
    {
        if ($objectClass && $objectClass != 'App\Items\Item') {
            return trans('reservation.unit-service');
        }
        return trans('reservation.unit');
    }
}