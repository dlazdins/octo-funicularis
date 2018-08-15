<?php

use App\Http\Controllers\Front\LanguagePageController;
use App\Http\Controllers\Front\TextPageController;
use App\Pages\LanguagePage;
use Arbory\Base\Admin\Form\Fields\Richtext;
use Arbory\Base\Admin\Form\Fields\Select;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Pages\TextPage;
use Illuminate\Support\Collection;
use Waavi\Translation\Models\Language;
use Waavi\Translation\Repositories\LanguageRepository;

Route::get('/', 'RootController@index')->name('index');

Page::register(LanguagePage::class)
    ->fields(function (FieldSet $fieldSet) {
        $languages = resolve(LanguageRepository::class)->all();
        /** @var Collection $languages */
        $languages = $languages->mapWithKeys(function (Language $language) {
            return [$language->getKey() => $language->getAttribute('name')];
        });
        $fieldSet->add((new Select('language_id'))->options($languages));
    })
    ->routes(function () {
        Route::get('/', LanguagePageController::class . '@index')->name('index');
    });

Page::register(TextPage::class)
    ->fields(function (FieldSet $fieldSet) {
        $fieldSet->add(new Richtext('html'));
    })
    ->routes(function () {
        Route::get('/', TextPageController::class . '@index')->name('index');
    });
