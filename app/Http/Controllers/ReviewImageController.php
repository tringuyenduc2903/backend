<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewImageRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReviewImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReviewImageRequest $request)
    {
        $image = $request->file('image');

        $disk = $this->getDisk();
        $path = $this->getPath();
        $file_name = $this->getFileName($disk, $path, $image);

        $disk->putFileAs($path, $image, $file_name);

        return ['file_name' => "$path/$file_name"];
    }

    protected function getDisk(): Filesystem
    {
        return Storage::disk('review');
    }

    protected function getPath(): string
    {
        return sprintf(
            '%s/%s',
            fortify_user()->id,
            Carbon::now()->format('Y/m')
        );
    }

    protected function getFileName(Filesystem $disk, string $path, UploadedFile $image): string
    {
        $mime_type = explode('/', $image->getMimeType());

        do {
            $file_name = sprintf(
                '%s_%s_%s.%s',
                $image->getClientOriginalName(),
                now()->timestamp,
                Str::random(5),
                Arr::last($mime_type),
            );
        } while ($disk->exists("$path/$file_name"));

        return $file_name;
    }
}
