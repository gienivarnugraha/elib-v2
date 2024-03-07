<?php

namespace App\Resources\Document\Cards;

use Illuminate\Http\Request;
use App\Core\Criteria\UserCriteria;
use App\Resources\Document\Criteria\ClosedDocuments as ClosedDocumentsCriteria;
use App\Core\Application\Charts\Progression;
use App\Contracts\Repositories\DocumentRepository;
use App\Core\Application\Cards\Card;

class TotalDocuments extends Card
{

  public ?string $name = 'Documents';

  public ?string $description = 'Total Documents';

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
    $repository = resolve(DocumentRepository::class);

    if ($user = $this->getUser()) {
      $repository->pushCriteria(new UserCriteria($user, 'assignee_id'));
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
