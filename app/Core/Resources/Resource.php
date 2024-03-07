<?php

namespace App\Core\Resources;

use JsonSerializable;
use App\Core\Facades\Menu;
use App\Core\Facades\Cards;
use Illuminate\Support\Str;
use App\Core\Facades\Fields;
use Illuminate\Http\Request;
use App\Core\Facades\Application;
use App\Core\Traits\ResolvesFilters;
use App\Core\Http\Request\ResourceRequest;
use App\Core\Contracts\Resources\Resourceful;
use App\Core\Resources\Traits\QueriesResources;
use App\Core\Application\Fields\Traits\ChangesKeys;
use App\Core\Application\Table\Traits\ResolveTables;
use App\Core\Contracts\Resources\ResourcefulRequestHandler;

abstract class Resource implements JsonSerializable
{
    use QueriesResources,
        ResolvesFilters,
        ResolveTables,
        ChangesKeys;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'id';

    /**
     * The direction the records should be default ordered by when retrieving
     */
    public static string $orderByDir = 'asc';

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = false;

    /**
     * Resource model
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $model;

    /**
     * Initialize new Resource class
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Get the underlying resource repository
     *
     * @return \App\Core\Repository\AppRepository
     */
    abstract public static function repository();

    /**
     * Get the resource underlying model class name
     *
     * @return string
     */
    public static function model()
    {
        return static::repository()->model();
    }

    /**
     * Set the resource model
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): ?string
    {
        return null;
    }

    /**
     * Create JSON resource
     *
     * @param  mixed  $data
     * @param  bool  $resolve Indicates whether to resolve the resource
     * @return mixed
     */
    public function createJsonResource($data)
    {
        $collection = is_countable($data);

        if ($collection) {
            $resource = $this->jsonResource()::collection($data);
        } else {
            $jsonResource = $this->jsonResource();
            $resource = new $jsonResource($data);
        }

        return $resource;
    }

    /**
     * Get the fields that should be included in JSON resource
     *
     * @param  \App\Core\Resources\Http\Request  $request
     * @param  \App\Core\Models\Model  $model
     * @return \App\Core\Fields\FieldsCollection
     */
    public function getFieldsForJsonResource($request, $model)
    {
        return $this->resolveFields()->reject(function ($field) use ($model) {
            return is_null($field->resolveForJsonResource($model));
        })->values();
    }

    /**
     * Set the available resource fields
     */
    public function fields(Request $request): array
    {
        return [];
    }

    /**
     * Get the resource defined fields
     *
     * @return \App\Core\Fields\FieldsCollection
     */
    public static function getFields()
    {
        return Fields::inGroup(static::name());
    }

    /**
     * Resolve the create fields for resource
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveCreateFields()
    {
        return Fields::resolveCreateFields(static::name());
    }

    /**
     * Resolve the update fields for the resource
     *
     * @return \App\Core\Fields\Collection
     */
    public function resolveUpdateFields()
    {
        return Fields::resolveUpdateFields(static::name());
    }

    /**
     * Resolve the resource fields for display
     *
     * @return \App\Core\Fields\FieldsCollection
     */
    public function resolveFields()
    {
        return static::getFields()->filter->authorizedToSee()->values();
    }

    /**
     * Set the resource rules available for create and update
     *
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for create
     *
     *
     * @return array
     */
    public function createRules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for update
     *
     *
     * @return array
     */
    public function updateRules(Request $request)
    {
        return [];
    }

    /**
     * Set the criteria that should be used to fetch only own data for the user
     */
    public function ownCriteria(): ?string
    {
        return null;
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): ?string
    {
        return null;
    }

    /**
     * Set the menu items for the resource
     */
    public function menu(): array
    {
        return [];
    }

    /**
     * Set  cards for the resource
     */
    public function cards(): array
    {
        return [];
    }

    /**
     * Get the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [];
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     */
    public function validationMessages(): array
    {
        return [];
    }

    /**
     * Determine whether the resource has associations
     */
    public function isAssociateable(): bool
    {
        return !is_null($this->associateableName());
    }

    /**
     * Get the resource available associative resources
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableAssociations()
    {
        return Application::registeredResources()
            ->reject(fn ($resource) => is_null($resource->associateableName()))
            ->filter(fn ($resource) => app(static::model())->isRelation($resource->associateableName()))
            ->values();
    }

    /**
     * Check whether the given resource can be associated to the current resource
     */
    public function canBeAssociated(string $resourceName): bool
    {
        return (bool) $this->availableAssociations()->first(
            fn ($resource) => $resource->name() == $resourceName
        );
    }

    /**
     * Get the resourceful CRUD handler class
     *
     * @param  \App\Core\Repository\AppRepository|null  $repository
     */
    public function resourcefulHandler(ResourceRequest $request, $repository = null): ResourcefulRequestHandler
    {
        $repository ??= static::repository();

        return count($this->fields($request)) > 0 ?
            new ResourcefulHandlerWithFields($request, $repository) :
            new ResourcefulHandler($request, $repository);
    }

    /**
     * Determine if this resource is searchable.
     */
    public static function searchable(): bool
    {
        return !empty(static::repository()->getFieldsSearchable());
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return Str::singular(static::label());
    }

    /**
     * Get the internal name of the resource.
     */
    public static function name(): string
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get the internal singular name of the resource.
     */
    public static function singularName(): string
    {
        return Str::singular(static::name());
    }

    /**
     * Get the resource importable class
     *
     * @return \App\Core\Resources\Import
     */
    // public function importable(): Import
    // {
    //   return new Import($this);
    // }

    /**
     * Get the resource import sample class
     *
     * @return \App\Core\Resources\ImportSample
     */
    // public function importSample(): ImportSample
    // {
    //   return new ImportSample($this);
    // }

    /**
     * Get the resource export class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     * @return \App\Core\Resources\Export
     */
    // public function exportable($repository): Export
    // {
    //   return new Export($this, $repository);
    // }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
    }

    /**
     * Register the resource available menu items
     */
    protected function registerMenuItems(): void
    {
        Application::booting(function () {
            foreach ($this->menu() as $item) {
                if (!$item->singularName) {
                    $item->singularName($this->singularLabel());
                }

                Menu::register($item);
            }
        });
    }

    /**
     * Register the resource available menu items
     */
    protected function registerSettingsMenuItems(): void
    {
        Application::booting(function () {
            foreach ($this->settingsMenu() as $key => $item) {
                // SettingsMenu::register($item, is_int($key) ? $this->name() : $key);
            }
        });
    }

    /**
     * Register the resource available CRUD fields
     */
    protected function registerFields(): void
    {
        if ($this instanceof Resourceful) {
            Fields::group($this->name(), function () {
                return $this->fields(request());
            });
        }
    }

    /**
     * Register the resource available cards
     */
    protected function registerCards(): void
    {
        Cards::register([
            'name' => $this->name(),
            'as'   => $this->label(),
        ], $this->cards());
    }

    /**
     * Register common permissions for the resource
     */
    protected function registerCommonPermissions(): void
    {
        if ($callable = config('core.resources.permissions.common')) {
            (new $callable)($this);
        }
    }

    /**
     * Register the resource information
     */
    protected function register(): void
    {
        $this->registerPermissions();
        $this->registerMenuItems();
        $this->registerSettingsMenuItems();
        $this->registerFields();
        $this->registerCards();
    }

    /**
     * Serialize the resource
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->label(),
            'singularLabel' => $this->singularLabel(),
        ];
    }
}
