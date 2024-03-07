<?php

namespace App\Resources\Setting;

use App\Core\Application\Table\Columns\HasOneColumn;

class SettingColumn extends HasOneColumn
{

  /**
   * Indicates whether the column is sortable
   */
  public bool $sortable = false;
}
