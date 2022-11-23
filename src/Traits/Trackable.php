<?php

namespace Hareland\Trackable\Traits;

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
        $this->trackedJobEnvelope = TrackableJobEnvelope::create([
            'trackable_id' => $this->getTrackable()->getKey(),
            'trackable_type' => get_class($this->getTrackable()),
            'handle' => class_basename(static::class),
        ]);
    }

    /**
     * @return Tracked[]
     */
    public function middleware(): array
    {
        return [new Tracked];
    }

    /**
     * Handle the job failing by marking the deployment as failed.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error($exception->getMessage());

        $message = $exception->getMessage();

        if ($exception instanceof MaxAttemptsExceededException) {
            $message = 'This operation took too long.';
        }

        $this->getTrackedJobEnvelope()->markAsFailed($message);
    }


    /**
     * @return Model
     */
    public function getTrackable(): Model
    {
        return $this->trackable;
    }

    /**
     * @return TrackableJobEnvelope
     */
    public function getTrackedJobEnvelope(): TrackableJobEnvelope
    {
        return $this->trackedJobEnvelope ?? new TrackableJobEnvelope();
    }
}
