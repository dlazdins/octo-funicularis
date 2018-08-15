<?php

use App\Pages\LanguagePage;
use Arbory\Base\Repositories\NodesRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Seeder;
use Waavi\Translation\Repositories\LanguageRepository;

class CubeDatabaseSeeder extends Seeder
{
    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var NodesRepository
     */
    protected $nodesRepository;

    public function __construct(
        LanguageRepository $languageRepository,
        NodesRepository $nodesRepository,
        DatabaseManager $databaseManager
    )
    {
        $this->languageRepository = $languageRepository;
        $this->databaseManager = $databaseManager;
        $this->nodesRepository = $nodesRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedLocales();
        $this->seedLanguageNodes();
    }

    /**
     * @return void
     */
    protected function seedLocales()
    {
        if ($this->languageRepository->getModel()->all()->isEmpty()) {
            $this->languageRepository->create([
                'locale' => 'en',
                'name' => 'English'
            ]);
        }
    }

    /**
     * @return void
     */
    protected function seedLanguageNodes()
    {
        if ($this->nodesRepository->getModel()->all()->isEmpty()) {

            $page = LanguagePage::firstOrCreate([
                'language_id' => 1
            ]);

            $this->nodesRepository->create([
                'name' => 'EN',
                'locale' => 'en',
                'slug' => 'en',
                'content_type' => \App\Pages\LanguagePage::class,
                'content_id' => $page->id,
                'active' => true,
            ]);
        }
    }

}
