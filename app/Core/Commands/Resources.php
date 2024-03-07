<?php

namespace App\Core\Commands;

use Error;
use App\Core\Support\Stub;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Core\Facades\Application;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class Resources extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'core:resources';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'See registered resources';

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return [];
  }

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $resources = Application::registeredResources();

    foreach ($resources as $key => $value) {
      $this->components->info($value::name());
    }
  }
}
