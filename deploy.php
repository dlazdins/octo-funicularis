<?php

namespace Deployer;

require 'vendor/deployer/deployer/recipe/laravel.php';

set('repository', 'git@git.cubesystems.lv:cube/boilerplate.git');
set('branch', 'master');
set('default_stage', 'staging');
set('compiled_assets', ['arbory', 'front', 'vendor', 'js', 'mix-manifest.json']);
set('writable_mode', 'chmod'); // chmod, chown, chgrp or acl.
set('writable_chmod_mode', '0775'); // For chmod mode
set('writable_chmod_recursive', true); // For chmod mode

/**
 * Hosts
 */
host('staging.cube.lv')
    ->user('boilerplate')
    ->stage('staging')
    ->forwardAgent(true)
    ->set('http_user', 'boilerplate')
    ->set('deploy_path', '/home/boilerplate/app');

/**
 * Setup task
 */
desc('Prepare your project');
task('setup', [
    'deploy:info',
    'deploy:prepare',
    'deploy:accept_host',
    'sync:db:up',
    'app:create_env',
    'app:generate_key'
]);

/**
 * Main task
 */
desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:accept_host',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:config:clear',
    'artisan:optimize',
    'artisan:migrate',
    'app:translations',
    'assets:build',
    'assets:upload',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

after('deploy', 'success');
after('deploy:failed', 'deploy:unlock');

/**
 * Tasks
 */
task('app:translations', function (){
    cd('{{release_path}}');
    run('php artisan translator:flush');
    run('php artisan translator:load');
});

task('deploy:accept_host', function (){
    run('ssh -oStrictHostKeyChecking=no -T git@git.cubesystems.lv');
});

task('app:generate_key', function (){
    cd('{{release_path}}');
    run('php artisan key:generate --no-interaction --force');
});

task('sync:db:up', function (){
    if (askConfirmation('Import database from local environment?')) {
        $remoteDb = ask('enter remote db name: ');
        $localDb = ask('enter local db name: ', $remoteDb);

        runLocally('mysqldump -u root -p ' . $localDb . ' > dump.sql');
        upload('dump.sql', '{{deploy_path}}/shared/dump.sql');
        run('mysql ' . $remoteDb . ' < {{deploy_path}}/shared/dump.sql');
        runLocally('rm dump.sql');
        run('rm {{deploy_path}}/shared/dump.sql');
    }
});

task('assets:build', function (){
    runLocally('yarn install');
    runLocally('yarn run prod');
});

task('assets:upload', function (){
    foreach (get('compiled_assets') as $compiledAsset) {
        upload('public/' . $compiledAsset, '{{release_path}}/public');
    }
});

task('app:create_env', function (){
    $envFileExists = run('if [ -s {{deploy_path}}/shared/.env ]; then echo \'true\'; else echo \'false\'; fi');
    if ($envFileExists === 'false') {
        upload('.env.example', '{{deploy_path}}/shared/.env');
        $mySqlPassword = run('grep password ~/.my.cnf  | perl -p -e \'s/password=//g\'');

        $envFileContent = run('cat {{deploy_path}}/shared/.env.example');
        $envFileContent = str_replace('DB_DATABASE=homestead', 'DB_DATABASE=' . get('http_user'), $envFileContent);
        $envFileContent = str_replace('DB_USERNAME=homestead', 'DB_USERNAME=' . get('http_user'), $envFileContent);
        $envFileContent = str_replace('DB_PASSWORD=secret', 'DB_PASSWORD=' . $mySqlPassword, $envFileContent);
        $envFileContent = str_replace('APP_ENV=local', 'APP_ENV=production', $envFileContent);

        $tmpFile = fopen('.env.tmp', 'w');
        fwrite($tmpFile, $envFileContent);
        fclose($tmpFile);

        upload('.env.tmp', get('deploy_path') . '/shared/.env');
        runLocally('rm .env.tmp');
    }
});


task('sync:down', [
    'sync:db:down',
    'sync:files:down'
]);

task('sync:up', [
    'sync:db:up',
    'sync:files:up'
]);

task('sync:files:down', function (){
    if (askConfirmation('Download public storage files from remote environment?')) {
        download('{{deploy_path}}/shared/storage/app/public/*', 'storage/app/public');
    }
});

task('sync:files:up', function (){
    if (askConfirmation('Import public storage files from local environment?')) {
        upload('public/storage/*', '{{deploy_path}}/shared/storage/app/public');
    }
});

task('sync:db:down', function (){
    if (askConfirmation('Download database from remote environment?')) {
        $remoteDb = ask('enter remote db name: ');
        $localDb = ask('enter local db name: ', $remoteDb);

        $stage = input()->getArgument('stage') ? input()->getArgument('stage') : get('default_stage');
        $gtidFix = in_array($stage, ['staging', 'staging_content']) ? '--set-gtid-purged=OFF' : '';

        run('mysqldump ' . $gtidFix . ' ' . $remoteDb . ' > {{deploy_path}}/shared/dump.sql');
        download('{{deploy_path}}/shared/dump.sql', 'dump.sql');
        runLocally('mysql ' . $localDb . ' < dump.sql');
        runLocally('rm dump.sql');
        run('rm {{deploy_path}}/shared/dump.sql');
    }
});


task('sync:db:up', function (){
    if (askConfirmation('Import database from local environment?')) {
        $remoteDb = ask('enter remote db name: ');
        $localDb = ask('enter local db name: ', $remoteDb);

        runLocally('mysqldump -u root -p ' . $localDb . ' > dump.sql');
        upload('dump.sql', '{{deploy_path}}/shared/dump.sql');
        run('mysql ' . $remoteDb . ' < {{deploy_path}}/shared/dump.sql');
        runLocally('rm dump.sql');
        run('rm {{deploy_path}}/shared/dump.sql');
    }
});


desc('Execute artisan config:clear');
task('artisan:config:clear', function (){
    run('{{bin/php}} {{release_path}}/artisan config:clear');
});



