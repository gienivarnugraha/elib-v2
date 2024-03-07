<?php

namespace App\Resources\Revision\Cards;

use App\Core\Criteria\UserCriteria;
use App\Core\Application\Cards\Card;
use App\Contracts\Repositories\DocumentRepository;
use App\Contracts\Repositories\RevisionRepository;

class TotalRevisions extends Card
{

  public ?string $name = 'Revision';

  public ?string $description = 'Total Revision';

  public function component(): string
  {
    return 'count-card';
  }

  /**
   * Calculates won deals by day
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return mixed
   */
  public function getData()
  {
    $repository = resolve(RevisionRepository::class);

    if ($user = $this->getUser()) {
      $repository->pushCriteria(new UserCriteria($user));
    }

    return $repository->count();
  }


  /**
   * Get the user for the card query
   *
   * @return mixed
   */
  protected function getUser()
  {
    if (request()->user()->can('view all documents')) {
      return request()->filled('user_id') ? (int) request()->user_id : null;
    }

    return auth()->user();
  }
}
