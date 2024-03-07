<?php

namespace App\Core\Models\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasAvatar
{

  /**
   * Get Gravatar URL
   */
  public function getGravatarUrl(string $email = null, string|int $size = '40'): string
  {
    $email ??= $this->email ?? '';

    return 'https://www.gravatar.com/avatar/' . md5(strtolower($email)) . '?s=' . $size;
  }

  /**
   * Get the model avatar URL.
   */
  public function avatarUrl(): Attribute
  {
    return Attribute::get(function () {
      if (is_null($this->avatar)) {
        return $this->getGravatarUrl();
      }

      return Storage::url($this->avatar);
    });
  }
}
