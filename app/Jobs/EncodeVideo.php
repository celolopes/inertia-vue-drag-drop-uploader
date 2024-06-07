<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Events\VideoEncodingStarted;
use App\Events\VideoEncodingProgress;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;

class EncodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Video $video)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        VideoEncodingStarted::dispatch($this->video);

        FFMpeg::fromDisk('public')
            ->open($this->video->path)
            ->export()
            ->afterSaving(function ($exporter, Media $media) {
                Storage::disk('public')->delete($this->video->path);

                $this->video->update([
                    'encoded' => true,
                    'path' => $media->getPath()
                ]);

                // dispatch an event that the video has been encoded
            })
            ->onProgress(function ($percentage) {
                VideoEncodingProgress::dispatch($this->video, $percentage);
            })
            ->toDisk('public')
            ->inFormat(new \FFMpeg\Format\Video\X264())
            ->save('videos/' . Str::uuid() . '.mp4');
    }
}
