<?php
namespace Tests\Commands;

use Tests\TestCase;
use App\Core\Support\Stub;
use Illuminate\Support\Str;
use App\Core\Support\FileGenerator;

class StubGeneratorTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_generate_stubs(): void
    {
        $name = Str::studly('test');

        dd( file_get_contents('app/Providers/CoreServiceProvider.php'));

        $stubs = [
            'model' => "app/Models/{$name}",
            'eloquent' => "app/Eloquent/{$name}Eloquent",
            'repository' => "app/Eloquent/{$name}Repository",
            'policy' => "app/Policies/{$name}Policy",
            'resources' => "app/Resources/{$name}/{$name}",
            'table' => "app/Resources/{$name}/{$name}Table",
            'factory' => "database/factories/{$name}Factory",
            'test' => "tests/Feature/Resources/Test/{$name}ResourceTest",
        ];

        $stubedFiles = [];

        foreach ($stubs as $stubFile => $path) {
            $content = (new Stub("/{$stubFile}.stub", [
                'CLASS'     => Str::studly($name),
                'VAR'       => Str::lower($name),
            ]))->render();

           

            $stubedFiles[] = new FileGenerator($path, $content);
        }
    }
}
