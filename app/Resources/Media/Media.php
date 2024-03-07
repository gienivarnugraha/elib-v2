<?php

namespace App\Resources\Media;

use App\Core\Resources\Resource;
use App\Core\Contracts\Resources\Resourceful;
use App\Contracts\Repositories\MediaRepository;

class Media extends Resource implements Resourceful
{

  /**
   * Get the underlying resource repository
   *
   * @return \App\Core\Repository\AppRepository
   */
  public static function repository()
  {
    return resolve(MediaRepository::class);
  }
}
