<?php

namespace App\Console\Commands;

use Arbory\Base\Console\Commands\InstallCommand;
use Cartalyst\Sentinel\Sentinel;
use Arbory\Base\Providers\ArboryServiceProvider;
use Arbory\Base\Services\StubRegistry;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use CubeDatabaseSeeder;

/**
 * Class InstallCommand
 * @package App\Console\Commands
 */
class CubeInstallCommand extends InstallCommand
{

    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $laravel;

    /**
     * @var string
     */
    protected $name = 'cube-agency:install';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var Sentinel
     */
    protected $sentinel;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var StubRegistry
     */
    protected $stubRegistry;

    /**
     * @return void
     */
    public function handle()
    {
        try {
            $this->databaseManager->connection();
        } catch (Exception $e) {
            $this->error('Unable to connect to the database.');
            $this->error('Please fill valid database credentials into .env and run this command again.');

            return;
        }

        $this->laravel['env'] = 'local';

        // Ask for database name
        $this->info('Setting up database...');
        $dbName = $this->ask('Enter a database name', $this->guessDatabaseName());

        // Set database credentials in .env
        $this->call('cube-agency:database', ['database' => $dbName]);
        $this->line('----Set database credentials in .env----');
        $this->call('key:generate');
        $this->call('storage:link');
        $this->line('-----------------------------------------');

        $this->runMigrations();
        $this->runSeeder();
        $this->publishFileManager();
        $this->publishLanguages();
        $this->createAdminUser();
        $this->npmDependencies();

        $this->info('Installation completed!');
    }

    /**
     * Guess database name from app folder.
     *
     * @return string
     */
    public function guessDatabaseName(): ?string
    {
        try {
            $segments = array_reverse(explode(DIRECTORY_SEPARATOR, app_path()));
            $name = explode('.', $segments[1])[0];

            return str_slug($name);
        } catch (Exception $e) {
            return '';
        }
    }


    /**
     * @return void
     */
    protected function publishLanguages()
    {
        $this->info('Publishing language resources');

        $this->call('vendor:publish', [
            '--provider' => ArboryServiceProvider::class,
            '--tag' => 'lang',
            '--force' => null,
        ]);

        $this->call('translator:load');
        $this->call('translator:flush');
    }

    /**
     * @return void
     */
    protected function runSeeder()
    {
        $this->info('Running database seeder');
        $this->call('db:seed', [
            '--class' => CubeDatabaseSeeder::class
        ]);
    }

    /**
     * @return void
     */
    protected function runMigrations()
    {
        $this->info('Running migrations');
        $this->call('migrate');
    }

    /**
     * @return void
     */
    protected function createAdminUser()
    {
        $users = $this->sentinel->getUserRepository();
        $activations = $this->sentinel->getActivationRepository();
        $roles = $this->sentinel->getRoleRepository();

        if ($users->all()->count() > 0) {
            $this->info('Admin user already exists');
            return;
        }

        $this->info('Creating support@cube.lv user');

        $user = null;

        try {
            $user = $users->create([
                'email' => 'support@cube.lv',
                'password' => 'admin123'
            ]);
            $activation = $activations->create($user);
            $activations->complete($user, $activation->getCode());
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
        }

        $administratorRole = $roles->create([
            'name' => 'Administrator',
            'slug' => 'administrator',
            'permissions' => array_flatten(
                array_merge(
                    [\Arbory\Base\Http\Controllers\Admin\DashboardController::class],
                    config('arbory.menu')
                )
            )
        ]);

        $administratorRole->users()->attach($user);
    }

    /**
     * @return void
     */
    protected function npmDependencies()
    {
        if (!`which yarn`) {
            $this->comment('Yarn not found. To complete the installation, run "yarn" and "yarn run dev" manually.');
            return;
        }

        $imagesDir = base_path() . '/resources/assets/images';
        if (!is_dir($imagesDir) && !mkdir($imagesDir) && !is_dir($imagesDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $imagesDir));
        }

        $this->info('Installing package.json dependencies');
        shell_exec('yarn install');

        $this->info('Compiling assets');
        shell_exec('yarn run dev');
    }
}
