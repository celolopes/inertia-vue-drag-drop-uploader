<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyVideoRequest;
use App\Http\Requests\UploadVideoRequest;
use App\Jobs\EncodeVideo;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateVideoRequest;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Handler\ContentRangeUploadHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;


class VideoController extends Controller
{
    public function store(Request $request) {
        $video = $request->user()->videos()->create([
            'title' => $request->title
        ]);

        return response()->json([
            'id' => $video->id,
        ]);
    }

    public function update(UpdateVideoRequest $request, Video $video)
    {
        $video->update($request->validated());

        return back()->with('success', 'Video atualizado com sucesso!');
    }

    public function destroy(DestroyVideoRequest $request, Video $video) {
        $video->delete();

        return back()->with('success','Vídeo excluído com sucesso!');
    }

    public function upload(UploadVideoRequest $request, Video $video)
    {
        $receiver = new FileReceiver(
            UploadedFile::fake()->createWithContent('file', $request->getContent()),
            $request,
            ContentRangeUploadHandler::class,
        );

        $save = $receiver->receive();

        if ($save->isFinished()) {
            return $this->saveAndStoreFile($save->getFile(), $video);
        }

        $save->handler();
    }

    protected function saveAndStoreFile(UploadedFile $file, Video $video) {
        $video->update(
            [
                'path' => $file->storeAs('videos', Str::uuid(), 'public'),
            ]
        );

        EncodeVideo::dispatch($video);
    }
}
