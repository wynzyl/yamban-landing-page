<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Throwable;

class GenerateDesignThumbnails implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 900;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $result = Process::path(base_path())
                ->timeout($this->timeout)
                ->run([
                    PHP_BINARY,
                    base_path('artisan'),
                    'designs:generate-thumbnails',
                ]);
        } catch (Throwable $exception) {
            Log::error('Design preview generation could not be started.', [
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if ($result->failed()) {
            Log::error('Design preview generation failed.', [
                'exit_code' => $result->exitCode(),
                'output' => $result->errorOutput(),
            ]);
        }
    }
}
