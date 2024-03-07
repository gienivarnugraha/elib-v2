<?php

namespace Tests\Feature\Resources;

use App\Core\Facades\Application;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ResourceTestCase extends TestCase
{
    use UseApiTest, UseFilterTest, UseRequest, UseResourceTest;
    // use TestsImportAndExport, TestsCustomFields;

    public $defaultData;

    protected $resourceName;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function data($data = [])
    {
        return array_merge($this->defaultData, $data);
    }

    protected function resource()
    {
        return Application::resourceByName($this->resourceName);
    }

    protected function model()
    {
        return $this->resource()->repository()->getModel();
    }

    protected function repository()
    {
        return $this->resource()->repository();
    }

    protected function record($id)
    {
        return $this->resource()->repository()->find($id);
    }

    protected function tableName()
    {
        return $this->model()->getTable();
    }

    protected function factory($count = null)
    {
        return $this->model()->factory($count);
    }

    protected function endpoint()
    {
        return "/api/{$this->resourceName}";
    }

    protected function indexEndpoint()
    {
        return $this->endpoint();
    }

    protected function createEndpoint()
    {
        return $this->endpoint();
    }

    protected function updateEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function showEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function deleteEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function forceDeleteEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "/api/trashed/{$this->resourceName}/{$id}";
    }

    protected function actionEndpoint($action)
    {
        $uriKey = (is_string($action) ? $this->findAction($action) : $action)->uriKey();

        return "/{$this->endpoint()}/actions/{$uriKey}/run";
    }

    protected function importUploadEndpoint()
    {
        return "/api/{$this->resourceName}/import/upload";
    }

    protected function importEndpoint($import)
    {
        $id = is_int($import) ? $import : $import->getKey();

        return "/api/{$this->resourceName}/import/{$id}";
    }

    protected function tableEndpoint($query = null)
    {
        return "/api/{$this->resourceName}/table?{$query}";
    }

    protected function tableSettingsEndpoint()
    {
        return "/api/{$this->resourceName}/table/settings";
    }

    public function pluck($array, $attribute)
    {
        if (is_array($array)) {
            return array_map(fn ($arr) => $arr->{$attribute}, $array);
        }

        if ($array instanceof Collection) {
            return $array->pluck($attribute)->toArray();
        }
    }

    protected function findAction($uriKey)
    {
        return collect($this->resource()->actions())
            ->first(fn ($action) => $action->uriKey() == $uriKey);
    }
}
