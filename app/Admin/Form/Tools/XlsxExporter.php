<?php

namespace App\Admin\Tools;

use Arbory\Base\Admin\Grid;
use Arbory\Base\Html\Html;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;

class XlsxExporter implements Renderable
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * Exporter constructor
     *
     * @param Grid $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $module = $this->grid->getModule();
        $url = route('admin.' . $module->name() . '.export', ['as' => 'xlsx']);

        return Html::div([
            Html::span(trans('admin/common.export'))->addClass('title'),
            Html::div(
                Html::link('xlsx')->addAttributes([
                    'type' => 'submit',
                    'href' => $url . '?' . request()->getQueryString()
                ])
            )->addClass('options')
        ])->addClass('export');
    }

    /**
     * @param Builder $rows
     * @param array $header
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function export(Builder $rows, array $header = [])
    {
        $batchSize = 30000;
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser($this->grid->getModule()->name() . '-export.xlsx');
        $style = (new StyleBuilder())->setFontSize(9)->setShouldWrapText(false)->build();
        if (!empty($header)) {
            $headerStyle = (new StyleBuilder())->setFontSize(9)->setFontBold()
                ->setBackgroundColor(Color::BLACK)->setFontColor(Color::WHITE)
                ->setShouldWrapText(false)->build();
            $writer->addRowWithStyle($header, $headerStyle);
        }

        $i = 0;
        while (!empty($data = $rows->take($batchSize)->skip($i)->get()->toArray())) {
            $writer->addRowsWithStyle($data, $style);
            $i = $i + $batchSize;
        }
        $writer->close();
        die;
    }

}