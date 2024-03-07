<?php

namespace App\Core\Contracts\Resources;

use App\Core\Application\Table\Table;
use Illuminate\Http\Request;

interface Tableable
{
    /**
     * Provide the resource table class
     *
     * @param  \App\Core\Repository\BaseRepository  $repository
     * @return  \App\Core\Application\Table\Table 
     */
    public function table($repository, Request $request): Table;
}
