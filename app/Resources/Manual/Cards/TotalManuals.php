<?php

namespace App\Resources\Manual\Cards;

use App\Core\Criteria\UserCriteria;
use App\Core\Application\Cards\Card;
use App\Contracts\Repositories\ManualRepository;
use App\Contracts\Repositories\DocumentRepository;

class TotalManuals extends Card
{

  public ?string $name = 'Manual';

  public ?string $description = 'Total Manual';

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
    $repository = resolve(ManualRepository::class);

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
