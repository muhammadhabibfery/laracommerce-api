<?php

namespace App\Traits;

use App\Http\Requests\API\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ImageHandler
{
    /**
     * create image file and save to application directory
     *
     * @param  Request|ProfileRequest $request
     * @param  string|null $fileName
     * @param  string $directory
     * @return void
     */
    private function createImage(Request|ProfileRequest $request, ?string $fileName = null, string $directory): void
    {

        if ($request->hasFile('image')) Storage::putFileAs($directory, $request->file('image'), $fileName);
    }

    /**
     * set the image file
     *
     * @param  Request|ProfileRequest $request
     * @param  string $directory
     * @param  string|null $oldImage
     * @return string|null
     */
    private function setImageFile(Request|ProfileRequest $request, string $directory, ?string $oldImage = null): string|null
    {
        $fileName = $oldImage;

        if ($request->hasFile('image')) {
            if ($oldImage) $this->deleteImage($directory, $oldImage);
            $fileName = explode('.', $request->file('image')->getClientOriginalName());
            $fileName = head($fileName) . rand(1, 100) . '.' . last($fileName);
        }

        return $fileName;
    }

    /**
     * delete the image file from application directory
     *
     * @param  string $directory
     * @param  string|null $paths
     * @return void
     */
    private function deleteImage(string $directory, ?string $paths = null): void
    {
        if ($paths) Storage::delete($directory . '/' . $paths);
    }
}
