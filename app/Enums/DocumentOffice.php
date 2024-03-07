<?php

namespace App\Enums;

use App\Core\Contracts\Enum;

enum DocumentOffice: string implements Enum
{
    use InteractWithEnumTrait;

    case DOA = 'DOA';
    case AMO = 'AMO';
}
