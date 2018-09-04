<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reservations\ReservationStatus;
use Arbory\Base\Admin\Form;
use Arbory\Base\Admin\Form\Fields\HasOne;
use Arbory\Base\Admin\Form\Fields\Hidden;
use Arbory\Base\Admin\Form\Fields\Text;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Admin\Grid;
use Arbory\Base\Admin\Traits\Crudify;
use Illuminate\Database\Eloquent\Model;
use App\Reservations\Reservation;

class ReservationsController extends Controller
{
    use Crudify;

    protected $resource = Reservation::class;

    /**
     * @param Model $model
     * @return Form
     */
    protected function form( Model $model )
    {
//        Admin::assets()->css('arbory/css/admin.css');

        $form = $this->module()->form($model, function (Form $form){
            $form->addField(new Hidden('id'));

            $form->addField((new Select('status'))
                ->options(ReservationStatus::getLabels())
                ->setLabel(trans('admin/reservation.status')));

            $form->addField((new Select('payment_type'))
                ->options(PaymentType::getLabels())
                ->setLabel(trans('admin/reservation.payment_type')));

            $form->addField(new HasOne('owner', function (FieldSet $fieldSet){
                $model = $fieldSet->getModel();
                if (method_exists($model, 'isLegal')) {
                    if ($model->isLegal()) {
                        $fieldSet->add((new HorizontalLine(trans('admin/reservation.person_type_legal'))));
                        $fieldSet->add((new Text('company_name'))->setLabel(trans('admin/reservation.company_name')));
                        $fieldSet->add((new Text('company_code'))->setLabel(trans('admin/reservation.company_code')));
                        $fieldSet->add((new Text('company_country'))->setLabel(trans('admin/reservation.company_country')));
                        $fieldSet->add((new Text('company_city'))->setLabel(trans('admin/reservation.company_city')));
                        $fieldSet->add((new Text('company_postal_code'))->setLabel(trans('admin/reservation.company_postal_code')));
                        $fieldSet->add((new Text('company_street'))->setLabel(trans('admin/reservation.company_street')));
                        $fieldSet->add((new Text('company_account'))->setLabel(trans('admin/reservation.company_account')));
                        $fieldSet->add((new Text('company_bank'))->setLabel(trans('admin/reservation.company_bank')));
                        $fieldSet->add((new Text('company_person'))->setLabel(trans('admin/reservation.company_person')));
                    } else {
                        $fieldSet->add((new HorizontalLine(trans('admin/reservation.person_type_private'))));
                        $fieldSet->add((new Text('first_name'))->setLabel(trans('admin/reservation.first_name')));
                        $fieldSet->add((new Text('last_name'))->setLabel(trans('admin/reservation.last_name')));
                    }
                }

                $fieldSet->add((new HorizontalLine(trans('admin/reservation.buyer_contacts')))
                    ->setLabel(trans('admin/reservation.buyer_contacts')));

                $fieldSet->add((new Text('phone'))->setLabel(trans('admin/reservation.phone')));
                $fieldSet->add((new Text('email'))->setLabel(trans('admin/reservation.email')));
                $fieldSet->add((new Textarea('comments'))->setLabel(trans('admin/reservation.comments')));
            }));

            $form->addField(new HorizontalLine(trans('admin/reservation.reservation')));
            $form->addField((new Table('name'))
                ->setHeader([
                    trans('admin/reservation.table.number'),
                    trans('admin/reservation.table.product'),
                    trans('admin/reservation.table.price'),
                    trans('admin/reservation.table.qty'),
                    trans('admin/reservation.table.product_total')
                ])->setBody($this->getFormattedRows($form->getModel())));
        });

        return $form;
    }

    /**
     * @param Reservation $reservation
     * @return array
     */
    private function getFormattedRows(Reservation $reservation)
    {
        $rows = [];
        $i = 1;

        foreach ($reservation->reservationLines as $line) {
            $rows[] = [
                $i++,
                $line->summary,
                Cart::pf($line->price, true),
                $line->quantity,
                Cart::pf($line->total, true)
            ];
        }

        $rows = collect($rows)->sortBy(0, SORT_NATURAL)->toArray();

        $rows[] = [
            null, null,
            Html::link($reservation->getIdentifier())->addAttributes([
                'href' => $this->url('invoice', ['reservationId' => $reservation->id]),
                'target' => '_blank',
                'style' => 'padding: 0;'
            ]),
            trans('admin/reservation.table.total') . ':',
            $reservation->amount_string
        ];

        return $rows;
    }

    /**
     * @return Grid
     */
    public function grid()
    {
//        Admin::assets()->css('arbory/css/admin.css');
        $request = request();

        $grid = new Grid($this->resource(), function (Grid $grid){
            $grid->column('created_at', trans('admin/reservation.created_at'))->sortable();
            $grid->column('id', trans('admin/reservation.invoice'))->display(function ($value, $column, $reservation){
                if (empty($reservation->getIdentifier())) return null;
                return Html::link($reservation->getIdentifier())->addAttributes([
                    'href' => $this->url('invoice', ['reservationId' => $reservation->id]),
                    'target' => '_blank'
                ]);
            });
            $grid->column('amount_string', trans('admin/reservation.table.total'));
            $grid->column('owner.person_type', trans('admin/reservation.person_type'))->display(function ($details){
                if (empty($details)) return null;
                return Html::span($details->person_type);
            });
            $grid->column('owner.name', trans('admin/reservation.name'))->display(function ($details){
                if (empty($details)) return null;
                return Html::span($details->name);
            });
            $grid->column('owner.email', trans('admin/reservation.email'))->display(function ($details){
                if (empty($details)) return null;
                return Html::span($details->email);
            });
            $grid->column('owner.phone', trans('admin/reservation.phone'))->display(function ($details){
                if (empty($details)) return null;
                return Html::span($details->phone);
            });
            $grid->column('payment_type', trans('admin/reservation.payment_type'))->display(function ($value, $column, $reservation){
                if (empty($reservation->payment_type)) return null;
                return Html::span(PaymentType::getLabel($reservation->payment_type));
            });
            $grid->column('status', trans('admin/reservation.status'))->display(function ($status){
                if (!isset($status)) return null;
                return new HtmlString(Html::span()
                        ->addClass('transaction-status')
                        ->addAttributes(['style="background-color: ' . ReservationStatus::getStatusColor($status) . ';"'])
                    . Html::span(ReservationStatus::getLabel($status))->addClass('transaction-label'));
            });

            $grid->column('transaction_error', trans('admin/reservation.error'))->display(function ($value) {
                return Html::span($value)->addClass('display-error');
            });
        });

        // custom grid filters
        $grid->filter(function (Filter $filter) use ($request){
            $filter->getQuery()->reservationBy(
                $request->get('_reservation_by', 'created_at'),
                $request->get('_reservation', 'desc')
            );
            $filter->setPerPage(50);
            $filter->getQuery()->where('owner_type', ReservationDetails::class);

            if ($from = request()->get('date_from')) {
                $filter->getQuery()->where('created_at', '>=', $from);
            }

            if ($to = request()->get('date_to')) {
                $filter->getQuery()->where('created_at', '<=', $to);
            }
        });

        $grid->setRenderer((new Builder($grid))->setFilterInputs($this->getFilterInputs()));
        $grid->addTool(new XlsxExporter($grid));

        $grid->setModule($this->module());

        return $grid;
    }

//    /**
//     * @return array
//     */
//    protected function getFilterInputs()
//    {
//        $inputs = [];
//        foreach (['date_from', 'date_to'] as $name) {
//            $inputs[] = Html::div([
//                Html::label(trans('admin/common.' . $name) . ':')->addAttributes(['for' => $name]),
//                Html::input()->setName($name)
//                    ->addClass('text datetime-picker')
//                    ->setValue(request()->get($name))
//                    ->addAttributes(['autocomplete' => 'off'])
//            ])->addClass('filter');
//        }
//
//        return $inputs;
//    }

//    /**
//     * @param Request $request
//     * @return mixed
//     */
//    public function invoice(Request $request)
//    {
//        $id = $request->get('reservationId');
//        $reservation = Reservation::query()->where('id', $id)->firstOrFail();
//        $invoice = new Invoice($reservation);
//        return $invoice->open();
//    }
//
//    public function export()
//    {
//        $rows = Reservation::query()
//            ->reservationBy('created_at', 'desc');
//
//        if ($from = request()->get('date_from')) {
//            $rows->where('created_at', '>=', $from);
//        }
//        if ($to = request()->get('date_to')) {
//            $rows->where('created_at', '<=', $to);
//        }
//
//        $header = [
//            'Added', 'ID', 'Number', 'Sum', 'Payment type', 'Status', 'Person type',
//            'First name', 'Last name', 'Email', 'Code', 'Contact person',
//            'Company name', 'Company account', 'Company bank', 'Company city', 'Company street',
//            'Company country', 'Company postal code',
//        ];
//
//        $batchSize = 1000;
//        $writer = WriterFactory::create(Type::XLSX);
//        $writer->openToBrowser('reservations-export.xlsx');
//        $style = (new StyleBuilder())->setFontSize(9)->setShouldWrapText(false)->build();
//        if (!empty($header)) {
//            $headerStyle = (new StyleBuilder())->setFontSize(9)->setFontBold()
//                ->setBackgroundColor(Color::BLACK)->setFontColor(Color::WHITE)
//                ->setShouldWrapText(false)->build();
//            $writer->addRowWithStyle($header, $headerStyle);
//        }
//
//        $i = 0;
//        while (($data = $rows->take($batchSize)->skip($i)->get())->count() > 0) {
//            $add = [];
//            /** @var Reservation $reservation */
//            foreach ($data as $reservation) {
//                /** @var ReservationDetails $owner */
//                $owner = $reservation->owner;
//                $add[] = [
//                    $reservation->created_at->format('Y-m-d H:i:s'),
//                    $reservation->id,
//                    $reservation->getIdentifier(),
//                    $reservation->amount_string,
//                    PaymentType::getLabel($reservation->payment_type),
//                    ReservationStatus::getLabel($reservation->status),
//                    !empty($owner) ? $owner->person_type : null,
//                    !empty($owner) ? $owner->first_name : null,
//                    !empty($owner) ? $owner->last_name : null,
//                    !empty($owner) ? $owner->email : null,
//                    !empty($owner) ? $owner->code : null,
//                    !empty($owner) ? $owner->company_person : null,
//                    !empty($owner) ? $owner->company_name : null,
//                    !empty($owner) ? $owner->company_account : null,
//                    !empty($owner) ? $owner->company_bank : null,
//                    !empty($owner) ? $owner->company_city : null,
//                    !empty($owner) ? $owner->company_street : null,
//                    !empty($owner) ? $owner->company_country : null,
//                    !empty($owner) ? $owner->company_postal_code : null,
//                ];
//            }
//
//            $writer->addRowsWithStyle($add, $style);
//            $i = $i + $batchSize;
//        }
//        $writer->close();
//        die;
//    }
}
