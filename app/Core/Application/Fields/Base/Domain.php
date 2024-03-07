<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.0.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2022 KONKORD DIGITAL
 */

namespace App\Core\Fields;

use App\Core\Resources\Http\ResourceRequest;

class Domain extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'domain-field';

    /**
     * This field support input group
     */
    public bool $supportsInputGroup = true;

    /**
     * Boot field
     *
     * Sets icon
     *
     * @return null
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 'example.com')->prependIcon('Globe');
    }

    /**
     * Get the field value for the given request
     *
     * @param  string  $requestAttribute
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute): mixed
    {
        $value = parent::attributeFromRequest($request, $requestAttribute);

        return \App\Core\Domain::extractFromUrl($value ?? '');
    }
}
