<?php

namespace App\Core\Application\Menu;

use App\Core\Traits\Authorizeable;
use App\Core\Traits\Makeable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

class Item implements Arrayable, JsonSerializable
{
    use Authorizeable, Makeable;

    /**
     * The menu item id
     */
    public string $id;

    /**
     * Item children
     */
    public array $children = [];

    /**
     * Singular name e.q. Contact, translates to Create "Contact"
     */
    public ?string $singularName = null;

    /**
     * Badge for the sidebar item
     *
     * @var mixed
     */
    public $badge = null;

    /**
     * Badge color variant
     */
    public string $badgeVariant = 'warning';

    /**
     * Does this item should be shown on quick create section
     */
    public bool $inQuickCreate = false;

    /**
     * Route for quick create
     */
    public ?string $quickCreateRoute = null;

    /**
     * Custom quick create name
     */
    public ?string $quickCreateName = null;

    /**
     * Initialize new Item instance.
     */
    public function __construct(public string $name, public string $route, public string $icon = '', public ?int $position = null)
    {
        $this->id = Str::slug($route);
    }

    /**
     * Set menu item id
     */
    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set badge for the menu item
     *
     * @param  mixed  $badge
     */
    public function badge(mixed $value): static
    {
        $this->badge = $value;

        return $this;
    }

    /**
     * Set badge variant
     */
    public function badgeVariant(string $value): static
    {
        $this->badgeVariant = $value;

        return $this;
    }

    /**
     * Get badge for the menu item
     */
    public function getBadge(): mixed
    {
        if ($this->badge instanceof \Closure) {
            return ($this->badge)();
        }

        return $this->badge;
    }

    /**
     * Set menu item position
     */
    public function position(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set menu item singular name
     */
    public function singularName(string $singular): static
    {
        $this->singularName = $singular;

        return $this;
    }

    /**
     * Register child menu item
     */
    public function withChild(self $item, string $id): static
    {
        $this->children[$id] = $item->id($id);

        return $this;
    }

    /**
     * Set the item child items
     */
    public function setChildren(array $items): static
    {
        $this->children = $items;

        return $this;
    }

    /**
     * Check if item has children
     *
     * @param  array  $items
     * @return static
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * Get the item child items
     *
     * @param  array  $items
     * @return static
     */
    public function getChildren(): array
    {
        return collect($this->children)->sortBy('position')->values()->all();
    }

    /**
     * Whether this item should be also included in the quick create section
     */
    public function inQuickCreate(bool $bool = true): static
    {
        $this->inQuickCreate = $bool;

        return $this;
    }

    /**
     * Custom quick create route
     * Default route is e.q. contacts/create
     */
    public function quickCreateRoute(string $route): static
    {
        $this->quickCreateRoute = $route;

        return $this;
    }

    /**
     * Custom quick create name
     */
    public function quickCreateName(string $name): static
    {
        $this->quickCreateName = $name;

        return $this;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        $menu = [
            'id' => $this->id,
            'name' => Str::title($this->name),
            'singularName' => $this->singularName,
            'route' => $this->route,
            'icon' => $this->icon,
            'position' => $this->position,
            'badge' => $this->getBadge(),
            'badgeVariant' => $this->badgeVariant,

        ];

        if ($this->inQuickCreate) {
            $menu['inQuickCreate'] = $this->inQuickCreate;
            $menu['quickCreateRoute'] = $this->quickCreateRoute;
            $menu['quickCreateName'] = $this->quickCreateName;
        }

        if ($this->hasChildren()) {
            $menu['children'] = $this->getChildren();
        }

        return $menu;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
