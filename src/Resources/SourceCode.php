<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class SourceCode extends Resource
{
    // The nested theme path's slashes must stay literal; percent-encoding them to %2F breaks HubSpot
    // routing, so encode each segment individually and keep '/' as the separator.
    private function contentPath(string $environment, string $path): string
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return "/cms/source-code/{$this->version()}/{$environment}/content/{$encoded}";
    }

    public function get(string $environment, string $path): string
    {
        return (string) $this->client->send('GET', $this->contentPath($environment, $path))->getBody();
    }

    /** @return array<mixed>|object */
    public function put(string $environment, string $path, string $content, ?string $filename = null): array|object
    {
        return $this->client->request('PUT', $this->contentPath($environment, $path), [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $content,
                    'filename' => $filename ?? basename($path),
                ],
            ],
        ]);
    }

    public function archive(string $environment, string $path): void
    {
        $this->client->send('DELETE', $this->contentPath($environment, $path));
    }
}
