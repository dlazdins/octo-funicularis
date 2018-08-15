<?php

namespace App\Console\Commands;


use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class Database extends Command
{

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cube-agency:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set database credentials in .env file';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new key generator command.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {

        $contents = $this->getKeyFile();

        $dbName = $this->argument('database');
        $dbUserName = $this->ask('What is your MySQL username?', 'root');

        $question = new Question('What is your MySQL password?', '<none>');
        $question->setHidden(true)->setHiddenFallback(true);
        $dbPassword = (new SymfonyQuestionHelper())->ask($this->input, $this->output, $question);
        if ($dbPassword === '<none>') {
            $dbPassword = '';
        }

       // Update DB credentials in .env file.
        $search = [
            '/('.preg_quote('DB_DATABASE=', null).')(.*)/',
            '/('.preg_quote('DB_USERNAME=', null).')(.*)/',
            '/('.preg_quote('DB_PASSWORD=', null).')(.*)/',
        ];
        $replace = [
            '$1'.$dbName,
            '$1'.$dbUserName,
            '$1'.$dbPassword,
        ];
        $contents = preg_replace($search, $replace, $contents);

        if (!$contents) {
            throw new Exception('Error while writing credentials to .env file.');
        }

        // Set DB username and password in config
        $this->laravel['config']['database.connections.mysql.username'] = $dbUserName;
        $this->laravel['config']['database.connections.mysql.password'] = $dbPassword;

        // Clear DB name in config
        unset($this->laravel['config']['database.connections.mysql.database']);

        // Force the new login to be used
        DB::purge();

        $createDatabase = $this->confirm('Create new database "<fg=yellow>'.$dbName.'</>"?');

        if($createDatabase) {
            // Create database if not exists
            DB::unprepared('CREATE DATABASE IF NOT EXISTS `'.$dbName.'` COLLATE `utf8mb4_general_ci`');
        }

        try{
            DB::unprepared('USE `'.$dbName.'`');
            DB::connection()->setDatabaseName($dbName);
        }catch (Exception $e){
            $this->error('Database "'.$dbName.'" does not exist!');
            die;
        }

        // Write to .env
        $this->files->put('.env', $contents);

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['database', InputArgument::REQUIRED, 'The database name']
        ];
    }

    /**
     * Get the key file and return its content.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getKeyFile(): string
    {
        return $this->files->exists('.env') ? $this->files->get('.env') : $this->files->get('.env.example');
    }
}
