<?php

namespace Hareland\Trackable\Models;

use Carbon\Carbon;
use Hareland\Trackable\Casts\StatusCast;
use Hareland\Trackable\Contracts\TrackableStatusColumn;
use Hareland\Trackable\Enums\Status;
use Hareland\Trackable\Traits\Trackable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $trackable_type
 * @property int $trackable_id
 * @property Trackable $trackable
 * @property string $handle
 * @property Status $status
 * @property array|null $data
 * @property Carbon $queued_at
 * @property Carbon $started_at
 * @property Carbon $finished_at
 * @property Carbon $failed_at
 */
class TrackableJobEnvelope extends Model
{
    protected $table = 'trackable_job_envelopes';

    protected $fillable = [
        'id',
        'trackable_type',
        'trackable_id',
        'handle',
        'status',
        'job_id',
        'data',
        'queued_at',
        'started_at',
        'finished_at',
        'failed_at',
    ];

    protected $casts = [
        'status' => StatusCast::class,
        'data' => 'array',
    ];

    protected $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
        'queued_at',
        'started_at',
        'finished_at',
        'failed_at',
    ];

    public function setStatus(TrackableStatusColumn $status, array $additional = [])
    {
        $this->update([
            'status' => $status,
            $status->column() => now(),
            ...$additional,
        ]);
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo('trackable');
    }

    public function setData(mixed $arrayable): void
    {
        $this->update(['data' => $arrayable]);
    }

    public function markAsFinished(mixed $data = []): void
    {
        if (!is_bool($data)) {
            $this->setStatus(Status::FINISHED, compact('data'));
        } else {
            $this->setStatus(Status::FINISHED);
        }
        if (method_exists($this->trackable, 'markAsFinished')) {
            $this->trackable->markAsFinished();
        }
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => Status::STARTED,
            'started_at' => now(),
        ]);
    }

    public function markAsFailed($message): void
    {
        if ($message instanceof Arrayable || is_array($message)) {
            $data = (array)$message;
        } elseif (is_scalar($message)) {
            $data = ['messages' => [$message]];
        } elseif ($message instanceof \Throwable) {
            $data = ['messages' => [$message->getMessage()]];
        }

        $this->update([
            'status' => Status::FAILED,
            'failed_at' => now(),
            'finished_at' => now(),
            'data' => $data,
        ]);

        //If our tracked model supports failing
        if (method_exists($this->trackable, 'markAsFailed')) {
            $this->trackable->markAsFailed();
        }
    }
}
