<?php

namespace App\Models;

use App\Core\Contracts\Presentable;
use App\Enums\DocumentOffice;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model implements Presentable
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no',
        'office',
        'type',
        'subject',
        'reference',
        'aircraft_id',
        'assignee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'office' => DocumentOffice::class,
        'type' => DocumentType::class,
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisable');
    }

    /**
     * A description of the entire PHP function.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query The query object.
     * @param  string  $type The type of aircraft.
     * @return mixed
     */
    public function scopeType(Builder $query, $type, $office)
    {
        if (!is_null($office)) {
            $query->where('office', '=', $office);
        }

        $query->where('type', '=', $type);

        $query->whereYear('created_at', date('Y', strtotime('now')));

        return $query;
    }

    /**
     * A description of the entire PHP function.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query The query object.
     * @param  string  $type The type of aircraft.
     * @return mixed
     */
    public function scopeOffice(Builder $query, $office)
    {
        return $query->where('office', '=', $office);
    }

    public function getLatestNumber($type, $office = null)
    {
        $count = $this->type($type, $office)->count();

        return str_pad(($count + 1), 3, '00', STR_PAD_LEFT);
    }

    public function generateNumber($docType, $office, $aircraft)
    {
        $month = date('m', strtotime('now'));
        $fullyear = date('Y', strtotime('now'));
        $year = date('y', strtotime('now'));

        $docType = DocumentType::value($docType);

        $onlyAircraftType = filter_var($aircraft, FILTER_SANITIZE_NUMBER_INT);

        $document = [
            // ! JE/002/MS1000/04/2023
            'JE' => 'JE/' . $this->getLatestNumber($docType) . '/MS1000/' . $month . '/' . $fullyear,

            // ! 235.AS.[E0/TD/ES].2020.001
            'DOA' => $onlyAircraftType . '.AS.' . $docType . '.' . $fullyear . '.' . $this->getLatestNumber($docType, 'DOA'),

            'AMO' => [
                // ! ES/CN235/MS1000/21-009
                'ES' => 'ES/' . $aircraft . '/MS1000/' . $year . '-' . $this->getLatestNumber($docType, 'AMO'),

                // ! CN235/MS1000/20-011
                'TD' => $aircraft . '/MS1000/' . $year . '-' . $this->getLatestNumber($docType, 'AMO'),

                // ! EO-235-2020-001
                'EO' => $docType . '-' . $onlyAircraftType . '-' . $fullyear . '-' . $this->getLatestNumber($docType, 'AMO'),
            ],
        ];

        if ($docType === 'JE') {
            return $document['JE'];
        } else {

            $office = DocumentOffice::value($office);

            return match ($office) {
                'DOA' => $document[$office],
                'AMO' => $document[$office][$docType],
            };
        }
    }

    public function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->no);
    }

    public function path(): Attribute
    {
        return Attribute::get(fn () => "/documents/{$this->id}");
    }
}
