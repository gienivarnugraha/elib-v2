<?php

namespace App\Core\Commands;

use App\Core\Support\Stub;
use Error;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class Generator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:generate {name} {--remove} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate model, eloquent, resource, model-resource, repository,policy,table,factory, tests.';

    /**
     * The resource name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * The resource name.
     *
     * @var string
     */
    protected $pluralize = '';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the generated in singular name.'],
            ['remove', InputArgument::OPTIONAL, 'Remove all generated class.'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->name = Str::studly($this->argument('name'));
        $this->pluralize = Str::plural(Str::lower(Str::snake($this->argument('name'))));

        $phpstubs = [
            'model' => "app/Models/{$this->name}",
            'eloquent' => "app/Eloquent/{$this->name}Eloquent",
            'resources' => "app/Http/Resources/{$this->name}Resource",
            'model-resources' => "app/Resources/{$this->name}/{$this->name}",
            'repository' => "app/Contracts/Repositories/{$this->name}Repository",
            'policy' => "app/Policies/{$this->name}Policy",
            'table' => "app/Resources/{$this->name}/{$this->name}Table",
            'factory' => "database/factories/{$this->name}Factory",
            'test' => "tests/Feature/Resources/Test/{$this->name}ResourceTest",
        ];

        $frontendStubs = [
            'index' => "resources/js/pages/{$this->pluralize}",
            'view' => "resources/js/pages/{$this->pluralize}/index",
            'update' => "resources/js/pages/{$this->pluralize}/[id]",
            'create' => "resources/js/pages/{$this->pluralize}/create",
        ];

        foreach ($phpstubs as $stub => $path) {
            $this->generate($stub, $path);
        }

        foreach ($frontendStubs as $stub => $path) {
            $this->generate($stub, $path, '.vue');
        }

        if (! $this->option('remove')) {
            $this->generateMigrationAndProvider();
        }
    }

    public function generateMigrationAndProvider()
    {
        $this->generateProvider();
        $this->generateMigration();
    }

    public function generateMigration()
    {
        $this->call('make:migration', ['name' => "create_{$this->pluralize}_table"]);
    }

    public function generateProvider()
    {
        (new Filesystem)->replaceInFile(
            '// auto-generate',

            '$this->app->bind(\App\Contracts\Repositories\\'.$this->name.'Repository::class,\App\Eloquent\\'.$this->name.'Eloquent::class);
// auto-generate',

            app_path('Providers\CoreServiceProvider.php')
        );
    }

    public function generate($stub, $destination, $extension = '.php')
    {
        $path = str_replace('\\', '/', $destination);
        $path .= $extension;

        if ($this->option('remove')) {

            if ((new Filesystem)->exists($path)) {
                (new Filesystem)->delete($path);
                $this->components->info("Deleted file {$path}");
            }
        } else {
            if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
                $this->laravel['files']->makeDirectory($dir, 0777, true);
            }

            $contents = (new Stub("/{$stub}.stub", [
                'CLASS' => $this->name,
                'VAR' => Str::lower($this->name),
            ]))->render();

            try {
                $this->components->task("Generating file {$path}", function () use ($path, $contents) {
                    $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;

                    if (! (new Filesystem)->exists($path)) {
                        (new Filesystem)->put($path, $contents);
                    } else {
                        throw new Error('File already exists!');
                    }

                    if ($overwriteFile === true) {
                        (new Filesystem)->put($path, $contents);
                    }

                    $this->components->info("Generated file {$path}");
                });

            } catch (Error $e) {
                $this->components->error("File : {$path} already exists.");

                return E_ERROR;
            }

            return 0;
        }
    }
}
