<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IPInfoWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ip;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->exceedRateLimit()) {
            // Если рейт-лимит превышен, добавляем IP обратно в очередь
            self::dispatch($this->ip)->delay(now()->addMinutes(5));
            return;
        }
    }
    protected function exceedRateLimit()
{
    $cacheExpiration = 3600;
    $requestsCount = Cache::get('ip_requests_' . $this->ip, 0);
    $requestsCount++;
    Cache::put('ip_requests_' . $this->ip, $requestsCount, $cacheExpiration);
    $maxRequests = 100;
    if ($requestsCount > $maxRequests) {
        return true;
    }
    return false;
}
}