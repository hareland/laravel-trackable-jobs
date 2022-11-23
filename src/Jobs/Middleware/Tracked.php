<?php

namespace Hareland\Trackable\Jobs\Middleware;

use App\Enums\CheckStatus;
use App\Lib\Scraping\Notifications\InvalidMerchantConfigurationNotification;
use App\Lib\Scraping\Notifications\PageNotFoundNotification;
use App\Lib\Scraping\Scraper\Exceptions\BrowserException;
use App\Lib\Scraping\Scraper\Exceptions\ConfigurationException;
use App\Lib\Scraping\Scraper\Exceptions\ScrapingException;
use Carbon\Carbon;
use Throwable;

class Tracked
{
    /**
     *
     * @param mixed $job
     * @param callable $next
     * @return void
     */
    public function handle($job, $next)
    {
        $job->getTrackedJobEnvelope()->markAsStarted();

        try {
            $response = call_user_func($next, $job);

            if ($response) {
                $job->getTrackedJobEnvelope()->markAsFinished($response);
            }
        } catch (BrowserException $exception) {
            $job->getConfiguration()->update([
                'check_status' => CheckStatus::COMPLETED,
                'last_checked_at' => now(),
            ]);
            $job->getTrackedJobEnvelope()->markAsFailed($exception);
        } catch (ConfigurationException $exception) {
            $job->getConfiguration()->update([
                'active' => false,
                'check_status' => CheckStatus::FAILED,
                'last_checked_at' => now(),
            ]);
            $job->getTrackedJobEnvelope()->markAsFailed($exception);

            //Notify site
            $job->getConfiguration()->product->site->notify(
                new InvalidMerchantConfigurationNotification(
                    $job->getTrackable()->configuration,
                    $exception,
                ),
            );
        } catch (ScrapingException $exception) {
            if ($exception->getCode() === ScrapingException::ERROR_NOTFOUND) {
                $job->getConfiguration()->update([
                    'active' => false,
                    'check_status' => CheckStatus::FAILED,
                    'last_checked_at' => now(),
                ]);
                $job->getTrackedJobEnvelope()->markAsFailed($exception);

            } elseif ($exception->getCode() === ScrapingException::ERROR_TIMEOUT) {
                $job->getConfiguration()->update([
                    'active' => false,
                    'check_status' => CheckStatus::FAILED,
                    'last_checked_at' => now(),
                ]);
                $job->getTrackedJobEnvelope()->markAsFailed($exception);

            } else {
                throw $exception;
            }
        } catch (Throwable $e) {
            $job->getConfiguration()->update([
                'active' => false,
                'check_status' => CheckStatus::FAILED,
                'last_checked_at' => now(),
            ]);
            $job->getTrackedJobEnvelope()->markAsFailed($e);
            $job->fail($e);
        }
    }
}
