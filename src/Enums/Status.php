<?php

namespace Hareland\Trackable\Enums;

use Hareland\Trackable\Contracts\TrackableStatusColumn;

enum Status: string implements TrackableStatusColumn
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case STARTED = 'started';
    case FAILED = 'failed';
    case FINISHED = 'finished';

    public function column(): string
    {
        return match ($this) {
            self::QUEUED => 'queued_at',
            self::STARTED => 'started_at',
            self::FAILED => 'failed_at',
            self::FINISHED => 'finished_at',
        };
    }
}
