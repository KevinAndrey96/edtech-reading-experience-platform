<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaUploadService
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
        ]);
    }

    /**
     * Upload an asset file to storage and optionally mirror it to an external storage server.
     */
    public function upload(UploadedFile $file, string $directory, string|int $identifier): string
    {
        $extension = $file->extension() ?: 'bin';
        $filename = sprintf('%s.%s', $identifier, $extension);
        $relativePath = sprintf('%s/%s', $directory, $filename);

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        $remoteUrl = config('services.media_mirror.url');
        if (! empty($remoteUrl)) {
            $this->mirrorToRemote($directory, $relativePath, $remoteUrl);
        }

        return '/storage/'.$relativePath;
    }

    /**
     * Mirror asset file to remote host.
     */
    protected function mirrorToRemote(string $directory, string $relativePath, string $remoteUrl): void
    {
        try {
            $absolutePath = Storage::disk('public')->path($relativePath);
            if (! file_exists($absolutePath)) {
                return;
            }

            $this->httpClient->request('POST', $remoteUrl, [
                'multipart' => [
                    [
                        'name' => 'image',
                        'contents' => fopen($absolutePath, 'r'),
                    ],
                    [
                        'name' => 'path',
                        'contents' => $directory,
                    ],
                ],
            ]);
        } catch (GuzzleException $e) {
            Log::warning('Remote media upload mirroring skipped: '.$e->getMessage());
        }
    }
}
