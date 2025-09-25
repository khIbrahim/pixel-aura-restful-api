<?php

namespace App\Services\V1\Media;

use App\Exceptions\V1\Media\UrlImageException;
use App\Jobs\V1\Media\CleanupTempFileJob;
use finfo;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class UrlImageDownloader
{
    private array $config;

    public function __construct()
    {
        $this->config = config('media-management.url_processing');
    }

    /**
     * @throws UrlImageException
     */
    public function download(string $url): string
    {
        $this->validateUrl($url);
        $this->checkDomainRestrictions($url);

        try {
            $response = Http::withOptions([
                'verify'  => true,
                'timeout' => $this->config['timeout'],
                'allow_redirects' => [
                    'max'             => $this->config['max_redirects'],
                    'strict'          => true,
                    'referer'         => true,
                    'track_redirects' => true
                ]
            ])->withHeaders([
                'User-Agent'    => $this->config['user_agent'],
                'Accept'        => 'image/*',
                'Cache-Control' => 'no-cache',
            ])->get($url);

            return $this->processResponse($response, $url);

        } catch (Throwable $e) {
            throw UrlImageException::downloadFailed($url, $e->getMessage());
        }
    }

    public function downloadMultiple(array $urls): array
    {
        $responses = Http::pool(fn (Pool $pool) => collect($urls)->map(
            fn ($url) => $pool->withOptions([
                'verify'  => true,
                'timeout' => $this->config['timeout'],
                'allow_redirects' => [
                    'max'         => $this->config['max_redirects'],
                    'strict'      => true,
                ]
            ])->withHeaders([
                    'User-Agent' => $this->config['user_agent'],
                    'Accept' => 'image/*',
            ])->get($url)
        ));

        $results = [];
        foreach ($urls as $index => $url) {
            try {
                if ($responses[$index]->successful()) {
                    $results[$url] = $this->processResponse($responses[$index], $url);
                } else {
                    $results[$url] = UrlImageException::downloadFailed(
                        $url,
                        "HTTP {$responses[$index]->status()}"
                    );
                }
            } catch (Throwable $e) {
                $results[$url] = UrlImageException::downloadFailed($url, $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * @throws UrlImageException
     */
    private function validateUrl(string $url): void
    {
        $validator = Validator::make(['url' => $url], [
            'url' => ['required', 'url', 'active_url']
        ]);

        if ($validator->fails()) {
            throw UrlImageException::invalidUrl($url);
        }

        $parsedUrl = parse_url($url);
        if (! in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
            throw UrlImageException::invalidUrl($url);
        }
    }

    /**
     * @throws UrlImageException
     */
    private function checkDomainRestrictions(string $url): void
    {
        $parsedUrl = parse_url($url);
        $host      = $parsedUrl['host'] ?? '';
        $ip        = gethostbyname($host);

        foreach ($this->config['blocked_domains'] as $blockedDomain) {
            if ($this->isDomainOrIpBlocked($host, $ip, $blockedDomain)) {
                throw UrlImageException::domainBlocked($host);
            }
        }

        if (! empty($this->config['allowed_domains'])) {
            $allowed = false;
            foreach ($this->config['allowed_domains'] as $allowedDomain) {
                if ($this->isDomainAllowed($host, $allowedDomain)) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                throw UrlImageException::domainBlocked($host);
            }
        }
    }

    private function isDomainOrIpBlocked(string $host, string $ip, string $blocked): bool
    {
        if ($host === $blocked) {
            return true;
        }

        if (str_ends_with($host, '.' . $blocked)) {
            return true;
        }

        if (str_contains($blocked, '/')) {
            return $this->ipInRange($ip, $blocked);
        }

        return $ip === $blocked;
    }

    private function isDomainAllowed(string $host, string $allowed): bool
    {
        return $host === $allowed || str_ends_with($host, '.' . $allowed);
    }

    private function ipInRange(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $subnet          = ip2long($subnet);
        $mask            = -1 << (32 - $bits);
        $subnet          &= $mask;

        return (ip2long($ip) & $mask) === $subnet;
    }

    /**
     * @throws UrlImageException
     */
    private function processResponse(Response $response, string $url): string
    {
        if (! $response->successful()) {
            throw UrlImageException::downloadFailed(
                $url,
                "HTTP {$response->status()}: {$response->reason()}"
            );
        }

        $contentType   = $response->header('Content-Type');
        $contentLength = $response->header('Content-Length');

        if ($contentLength && (int) $contentLength > $this->config['security']['max_file_size']) {
            throw UrlImageException::fileTooLarge(
                (int) $contentLength,
                $this->config['security']['max_file_size']
            );
        }

        if (! str_starts_with($contentType, 'image/')) {
            throw UrlImageException::invalidImageFormat($contentType);
        }

        $imageContent = $response->body();

        if (strlen($imageContent) > $this->config['security']['max_file_size']) {
            throw UrlImageException::fileTooLarge(
                strlen($imageContent),
                $this->config['security']['max_file_size']
            );
        }

        if ($this->config['security']['check_image_headers']) {
            $this->validateImageHeaders($imageContent);
        }

        return $this->saveTemporaryFile($imageContent, $contentType);
    }

    /**
     * @throws UrlImageException
     */
    private function validateImageHeaders(string $imageContent): void
    {
        $finfo        = new finfo(FILEINFO_MIME_TYPE);
        $detectedType = $finfo->buffer($imageContent);

        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png',
            'image/gif', 'image/webp', 'image/bmp'
        ];

        if (! in_array($detectedType, $allowedTypes)) {
            throw UrlImageException::invalidImageFormat($detectedType);
        }

        $imageInfo = getimagesizefromstring($imageContent);
        if ($imageInfo === false) {
            throw UrlImageException::invalidImageFormat('Données d\'image corrompues');
        }
    }

    private function saveTemporaryFile(string $content, string $contentType): string
    {
        $extension = $this->getExtensionFromMimeType($contentType);
        $fileName  = 'url_' . Str::uuid() . '.' . $extension;
        $tempPath  = $this->config['temp_storage']['path'] . '/' . $fileName;

        Storage::disk($this->config['temp_storage']['disk'])->put($tempPath, $content);

        $this->scheduleCleanup($tempPath);

        return Storage::disk($this->config['temp_storage']['disk'])->path($tempPath);
    }

    private function getExtensionFromMimeType(string $mimeType): string
    {
        return match($mimeType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            default => 'jpg'
        };
    }

    private function scheduleCleanup(string $tempPath): void
    {
        CleanupTempFileJob::dispatch($tempPath)
            ->delay(now()->addSeconds($this->config['temp_storage']['cleanup_after']));
    }
}
