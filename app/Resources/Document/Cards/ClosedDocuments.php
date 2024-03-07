<?php

namespace App\Resources\Document\Cards;

use Illuminate\Http\Request;
use App\Core\Criteria\UserCriteria;
use App\Resources\Document\Criteria\ClosedDocuments as ClosedDocumentsCriteria;
use App\Core\Application\Charts\Progression;
use App\Contracts\Repositories\DocumentRepository;

class ClosedDocuments extends Progression
{
  /**
   * Calculates won deals by day
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return mixed
   */
  public function calculate(Request $request)
  {
    $repository = resolve(DocumentRepository::class)->pushCriteria(ClosedDocumentsCriteria::class);

    if ($user = $this->getUser()) {
      $repository->pushCriteria(new UserCriteria($user, 'assignee_id'));
    }

    return $this->countByDays($request, $repository);
  }

  /**
   * Get the ranges available for the chart.
   *
   * @return array
   */
  public function ranges(): array
  {
    return [
      7  => '7 days',
      15 => '15 days',
      30 => '30 days',
      60 => '60 days',
    ];
  }

  /**
   * The card name
   *
   * @return string
   */
  public function name(): string
  {
    return 'Closed Documents';
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
