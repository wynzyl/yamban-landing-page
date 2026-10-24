<?php

namespace App\Console\Commands;

use App\Models\Design;
use App\Support\DesignThumbnail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('designs:generate-thumbnails {--design= : Generate a preview for one design ID} {--force : Regenerate previews that already exist} {--local : Only import existing local preview files}')]
#[Description('Store design previews in the database, generating them from Google Drive when needed.')]
class GenerateDesignThumbnails extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DesignThumbnail $thumbnails): int
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $designId = $this->option('design');
        $force = (bool) $this->option('force');
        $localOnly = (bool) $this->option('local');

        $query = Design::query()->orderBy('id');

        if (filled($designId)) {
            $query->whereKey((int) $designId);
        } elseif (! $force) {
            $query->where('has_preview', false);
        }

        $designs = $query->get();

        if ($designs->isEmpty()) {
            $this->info('No designs need previews.');

            return self::SUCCESS;
        }

        $lock = Cache::lock('design-thumbnails', 1800);

        if (! $lock->get()) {
            $this->warn('Another design preview generation is already running.');

            return self::SUCCESS;
        }

        $failed = 0;
        $skipped = 0;

        try {
            foreach ($designs as $design) {
                $this->line("Generating preview for {$design->name}...");

                if ($localOnly) {
                    if ($thumbnails->storeFromLocalFile($design)) {
                        $this->info('  Saved from local file.');
                    } else {
                        $skipped++;
                        $this->line('  No local preview file; skipped.');
                    }

                    continue;
                }

                if (! $force && $thumbnails->storeFromLocalFile($design)) {
                    $this->info('  Saved from local file.');

                    continue;
                }

                if ($thumbnails->generate($design, $force)) {
                    $this->info('  Saved.');
                } else {
                    $failed++;
                    $this->error('  Could not generate a preview.');
                }
            }
        } finally {
            $lock->release();
        }

        if ($skipped > 0) {
            $this->line("Skipped {$skipped} designs without a local preview file.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
