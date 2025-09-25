<?php

namespace App\Jobs\V1\Media;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CleanupTempFileJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $filePath
    ){}

    public function handle(): void
    {
        try {
            $disk = config('media-management.url_processing.temp_storage.disk') ?? 'local';

            $relativePath = str_replace(
                Storage::disk($disk)->path(''),
                '',
                $this->filePath
            );

            if (Storage::disk($disk)->exists($relativePath)) {
                Storage::disk($disk)->delete($relativePath);
                Log::info("Fichier temporaire supprimé", ['file_path' => $this->filePath]);
            }
        } catch (Throwable $e) {
            Log::warning("Échec de la suppression du fichier temporaire", [
                'file_path' => $this->filePath,
                'error'     => $e->getMessage()
            ]);
        }

    }
}
