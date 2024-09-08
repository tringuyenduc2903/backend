<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Response as ResponseFacade;

class SwaggerController extends \L5Swagger\Http\Controllers\SwaggerController
{
    /**
     * Dump api-docs content endpoint. Supports dumping a json, or yaml file.
     *
     * @throws FileNotFoundException
     */
    public function docs(Request $request): Response
    {
        $fileSystem = new Filesystem;
        $config = $request->offsetGet('config');
        $file = $request->offsetGet('jsonFile');

        $targetFile = $config['paths']['docs_json'] ?? 'api-docs.json';
        $yaml = false;

        if ($file !== null) {
            $targetFile = $file;
            $parts = explode('.', $file);

            if (! empty($parts)) {
                $extension = array_pop($parts);
                $yaml = strtolower($extension) === 'yaml';
            }
        }

        $filePath = $config['paths']['docs'].'/'.$targetFile;

        if ($config['generate_always']) {
            Process::run('swagger-cli bundle ../resources/yaml/index.yaml --outfile ../storage/api-docs/api-docs.yaml --type yaml');
        }

        if (! $fileSystem->exists($filePath)) {
            abort(404, sprintf('Unable to locate documentation file at: "%s"', $filePath));
        }

        $content = $fileSystem->get($filePath);

        if ($yaml) {
            return ResponseFacade::make($content, 200, [
                'Content-Type' => 'application/yaml',
                'Content-Disposition' => 'inline',
            ]);
        }

        return ResponseFacade::make($content, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
