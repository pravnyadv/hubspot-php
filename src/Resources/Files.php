<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Files extends Resource
{
    private function base(): string
    {
        return "/files/{$this->version()}/files";
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<mixed>|object
     */
    public function upload(string $contents, string $fileName, array $options = [], ?string $folderPath = null): array|object
    {
        $multipart = [
            [
                'name' => 'file',
                'contents' => $contents,
                'filename' => $fileName,
            ],
            [
                'name' => 'options',
                'contents' => (string) json_encode($options ?: new \stdClass),
            ],
        ];

        if ($folderPath !== null) {
            $multipart[] = [
                'name' => 'folderPath',
                'contents' => $folderPath,
            ];
        }

        return $this->client->request('POST', $this->base(), [
            'multipart' => $multipart,
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $fileId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$fileId}");
    }

    /**
     * "{base}" itself is POST-only (upload); the real listing is "{base}/search".
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/search", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function signedUrl(string $fileId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$fileId}/signed-url");
    }

    public function download(string $fileId): string
    {
        return (string) $this->client->send('GET', "{$this->base()}/{$fileId}/download")->getBody();
    }

    public function delete(string $fileId): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$fileId}");
    }
}
