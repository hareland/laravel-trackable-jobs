<?php

namespace Hareland\Trackable\Traits;

use Hareland\Trackable\Contracts\Middleware;
use Hareland\Trackable\Jobs\Middleware\Tracked;
use Hareland\Trackable\Models\TrackableJobEnvelope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Facades\Log;
use Throwable;

trait Trackable
{


    /**
     * TrackedJob tied to this job.
     *
     * @var TrackableJobEnvelope
     */
    protected ?TrackableJobEnvelope $trackedJobEnvelope = null;

    public function __construct(
        protected Model $trackable,
    )
    {
    }

    public function before(): void
    {
        $this->trackedJobEnvelope = TrackableJobEnvelope::create([
            'trackable_id' => $this->getTrackable()->getKey(),
            'trackable_type' => get_class($this->getTrackable()),
            'handle' => class_basename(static::class),
        ]);

        if (!empty($uuid = $this->job->uuid())) {
            $this->getTrackedJobEnvelope()->update([
                'job_uuid' => $uuid,
            ]);
        }
    }

    /**
     * @return Middleware[]
     */
    public function middleware(): array
    {
        return [app(Middleware::class)];
    }

    public function markAsFinished(array $data = []): bool
    {
        $this->getTrackedJobEnvelope()->markAsFinished($data);
        return true;
    }


    public function failed(\Throwable $exception)
    {
        Log::error($exception->getMessage());

        $message = $exception->getMessage();

        if ($exception instanceof MaxAttemptsExceededException) {
            $message = 'This operation took too long.';
        }

        $this->markAsFailed($message);
    }

    public function markAsFailed($message): bool
    {
        $this->getTrackedJobEnvelope()->markAsFailed($message);
        return true;
    }

    public function getTrackable(): Model
    {
        return $this->trackable;
    }

    public function getTrackedJobEnvelope(): TrackableJobEnvelope
    {
        return $this->trackedJobEnvelope ?? new TrackableJobEnvelope();
    }
}
