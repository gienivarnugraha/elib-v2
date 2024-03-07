<?php

namespace App\Resources\Document\Filters;

use Illuminate\Support\Facades\Auth;
use App\Core\Application\Filters\Filter;

class OwnDocumentFilter extends Filter
{
    public function __construct()
    {
        parent::__construct('assignee_id', 'Owned Document');

        $this->setOperator('equal')
            ->setValue('me')
            ->toArray();
    }

    public function type(): string
    {
        return 'static';
    }
}
