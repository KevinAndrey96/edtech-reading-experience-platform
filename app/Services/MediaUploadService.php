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
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|int $identifier
     * @return string
     */
    public function upload(UploadedFile $file, string $directory, string|int $identifier): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = sprintf('%s.%s', $identifier, $extension);
        $relativePath = sprintf('%s/%s', $directory, $filename);

        // Store file locally in public disk
        Storage::disk('public')->putFileAs($directory, $file, $filename);

        // Mirror to remote upload endpoint if configured
        $remoteUrl = env('UPLOAD_REMOTE_URL');
        if (!empty($remoteUrl)) {
            $this->mirrorToRemote($directory, $relativePath, $remoteUrl);
        }

        return '/storage/' . $relativePath;
    }

    /**
     * Mirror asset file to remote host.
     *
     * @param string $directory
     * @param string $relativePath
     * @param string $remoteUrl
     * @return void
     */
    protected function mirrorToRemote(string $directory, string $relativePath, string $remoteUrl): void
    {
        try {
            $absolutePath = Storage::disk('public')->path($relativePath);
            if (!file_exists($absolutePath)) {
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
            Log::warning('Remote media upload mirroring skipped: ' . $e->getMessage());
        }
    }
}
