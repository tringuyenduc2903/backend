<?php

namespace App\Library\Uploaders;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SingleBase64Image extends \Backpack\CRUD\app\Library\Uploaders\SingleBase64Image
{
    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null): array
    {
        foreach ($values as $row => $rowValue) {
            if ($rowValue) {
                if (Str::startsWith($rowValue, 'data:image')) {
                    $base64Image = Str::after($rowValue, ';base64,');
                    $finalPath = $this->getPath().$this->getFileName($rowValue);
                    Storage::disk($this->getDisk())->put($finalPath, base64_decode($base64Image));
                    $values[$row] = $previousRepeatableValues[] = $finalPath;
                }
            }
        }

        $imagesToDelete = array_diff($previousRepeatableValues, $values);

        foreach ($imagesToDelete as $image) {
            if ($image) {
                Storage::disk($this->getDisk())->delete($image);
            }
        }

        return $values;
    }
}
