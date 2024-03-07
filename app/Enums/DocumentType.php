<?php

namespace App\Enums;

use App\Core\Contracts\Enum;

enum DocumentType: string implements Enum
{
    use InteractWithEnumTrait;

    case JE = 'JE';
    case ES = 'ES';
    case EO = 'EO';
    case TD = 'TD';
}
