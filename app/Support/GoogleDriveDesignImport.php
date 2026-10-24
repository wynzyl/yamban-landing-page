<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Design;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveDesignImport
{
    /**
     * Drive API endpoint used to list and read files.
     */
    private const FILES_ENDPOINT = 'https://www.googleapis.com/drive/v3/files';

    /**
     * MIME type Google Drive uses for folders.
     */
    private const FOLDER_MIME = 'application/vnd.google-apps.folder';

    /**
     * Image MIME types accepted by the design library.
     *
     * @var array<int, string>
     */
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
    ];

    /**
     * Save every Drive folder or file link as a design in the category.
     *
     * Folder links are expanded recursively. File links may be written as
     * "Design name | link" to override the Drive filename.
     *
     * @return array{imported: int, skipped: int, invalid: int, folders: int, files: int, non_images: int}
     */
    public function import(string $links, Category $category): array
    {
        $apiKey = (string) config('services.google.drive_api_key');
        $sortOrder = (int) Design::query()->where('category_id', $category->id)->max('sort_order');
        $seen = [];
        $visited = [];
        $stats = [
            'imported' => 0,
            'skipped' => 0,
            'invalid' => 0,
            'folders' => 0,
            'files' => 0,
            'non_images' => 0,
        ];

        foreach (preg_split('/\R/', $links) ?: [] as $line) {
            $name = null;

            if (str_contains($line, '|')) {
                [$name, $line] = array_map('trim', explode('|', $line, 2));
                $name = $name !== '' ? $name : null;
            }

            foreach (preg_split('/[\s,;]+/', trim($line)) ?: [] as $link) {
                $link = html_entity_decode(trim($link, " \t\n\r\0\x0B<>\"'()[].,;"));

                if ($link === '') {
                    continue;
                }

                if (! str_contains($link, '://')) {
                    $link = 'https://'.$link;
                }

                if (! self::isDriveLink($link)) {
                    $stats['invalid']++;

                    continue;
                }

                if (($folderId = self::folderId($link)) !== null) {
                    $this->importFolder($folderId, $category, $apiKey, $sortOrder, $seen, $visited, $stats);

                    continue;
                }

                if (($fileId = self::fileId($link)) !== null) {
                    $this->importFile($fileId, $link, $name, $category, $apiKey, $sortOrder, $seen, $stats);

                    continue;
                }

                if (($id = self::idQuery($link)) !== null) {
                    $metadata = $this->metadata($id, $apiKey);

                    if (($metadata['mimeType'] ?? null) === self::FOLDER_MIME) {
                        $this->importFolder($id, $category, $apiKey, $sortOrder, $seen, $visited, $stats);
                    } else {
                        $this->importFile($id, $link, $name, $category, $apiKey, $sortOrder, $seen, $stats, $metadata);
                    }

                    continue;
                }

                $stats['invalid']++;
            }
        }

        return $stats;
    }

    /**
     * Determine if a link points at Google Drive or Google Docs.
     */
    public static function isDriveLink(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && in_array(strtolower($host), [
            'drive.google.com',
            'docs.google.com',
            'drive.usercontent.google.com',
        ], true);
    }

    /**
     * Determine if a MIME type is a supported design image.
     */
    private static function isImageMimeType(string $mimeType): bool
    {
        return in_array($mimeType, self::IMAGE_MIME_TYPES, true);
    }

    /**
     * Extract the folder ID from a shared Google Drive link.
     */
    public static function folderId(string $url): ?string
    {
        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract a file ID from a shared Google Drive link.
     */
    public static function fileId(string $url): ?string
    {
        if (preg_match('#/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract an ID from an open or download link.
     */
    public static function idQuery(string $url): ?string
    {
        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Import every file inside a folder and its subfolders.
     *
     * @param  array<string, true>  $seen
     * @param  array<string, true>  $visited
     * @param  array{imported: int, skipped: int, invalid: int, folders: int, files: int, non_images: int}  $stats
     */
    private function importFolder(
        string $folderId,
        Category $category,
        string $apiKey,
        int &$sortOrder,
        array &$seen,
        array &$visited,
        array &$stats,
    ): void {
        if (isset($visited[$folderId])) {
            return;
        }

        $visited[$folderId] = true;
        $stats['folders']++;

        foreach ($this->listFolder($folderId, $apiKey) as $item) {
            $id = (string) ($item['id'] ?? '');

            if ($id === '') {
                continue;
            }

            $mimeType = (string) ($item['mimeType'] ?? '');

            if ($mimeType === self::FOLDER_MIME) {
                $this->importFolder($id, $category, $apiKey, $sortOrder, $seen, $visited, $stats);

                continue;
            }

            $stats['files']++;

            if ($mimeType !== '' && ! self::isImageMimeType($mimeType)) {
                $stats['non_images']++;

                continue;
            }

            $this->saveDesign(
                $id,
                (string) ($item['name'] ?? ''),
                (string) ($item['webViewLink'] ?? self::fileUrl($id)),
                $category,
                $sortOrder,
                $seen,
                $stats,
            );
        }
    }

    /**
     * Import a single file link.
     *
     * @param  array<string, mixed>|null  $metadata
     * @param  array<string, true>  $seen
     * @param  array{imported: int, skipped: int, invalid: int, folders: int, files: int, non_images: int}  $stats
     */
    private function importFile(
        string $fileId,
        string $url,
        ?string $name,
        Category $category,
        string $apiKey,
        int &$sortOrder,
        array &$seen,
        array &$stats,
        ?array $metadata = null,
    ): void {
        $stats['files']++;

        if (isset($seen[$fileId]) || $this->designExists($category, $fileId)) {
            $stats['skipped']++;

            return;
        }

        if ($name === null && $metadata === null) {
            $metadata = $this->metadata($fileId, $apiKey);
        }

        $mimeType = (string) ($metadata['mimeType'] ?? '');

        if ($mimeType !== '' && ! self::isImageMimeType($mimeType)) {
            $stats['non_images']++;

            return;
        }

        $resolvedName = $name ?? trim((string) ($metadata['name'] ?? '')) ?: 'Design '.$fileId;
        $resolvedUrl = (string) ($metadata['webViewLink'] ?? $url);

        $this->saveDesign($fileId, $resolvedName, $resolvedUrl, $category, $sortOrder, $seen, $stats);
    }

    /**
     * Create the design when it does not already exist in the category.
     *
     * @param  array<string, true>  $seen
     * @param  array{imported: int, skipped: int, invalid: int, folders: int, files: int, non_images: int}  $stats
     */
    private function saveDesign(
        string $id,
        string $name,
        string $url,
        Category $category,
        int &$sortOrder,
        array &$seen,
        array &$stats,
    ): void {
        if (isset($seen[$id]) || $this->designExists($category, $id)) {
            $stats['skipped']++;

            return;
        }

        $seen[$id] = true;

        Design::query()->create([
            'category_id' => $category->id,
            'name' => trim($name) !== '' ? trim($name) : 'Design '.$id,
            'drive_url' => $url,
            'thumbnail_url' => null,
            'is_active' => true,
            'sort_order' => ++$sortOrder,
        ]);

        $stats['imported']++;
    }

    /**
     * Determine if the design already exists in the category.
     */
    private function designExists(Category $category, string $driveId): bool
    {
        return Design::query()
            ->where('category_id', $category->id)
            ->where('drive_url', 'like', '%'.$driveId.'%')
            ->exists();
    }

    /**
     * List every item directly inside a Drive folder, following pagination.
     *
     * @return array<int, array<string, mixed>>
     */
    private function listFolder(string $folderId, string $apiKey): array
    {
        if ($apiKey === '') {
            throw new RuntimeException(__('Set GOOGLE_DRIVE_API_KEY in your .env file to import a Google Drive folder.'));
        }

        $items = [];
        $pageToken = null;

        do {
            $response = Http::timeout(30)->get(self::FILES_ENDPOINT, array_filter([
                'key' => $apiKey,
                'q' => "'{$folderId}' in parents and trashed = false",
                'fields' => 'nextPageToken, files(id, name, mimeType, webViewLink)',
                'pageSize' => 1000,
                'pageToken' => $pageToken,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]));

            if ($response->failed()) {
                throw new RuntimeException($this->errorMessage($response));
            }

            $items = array_merge($items, $response->json('files') ?? []);
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return $items;
    }

    /**
     * Read the metadata for one Drive file.
     *
     * @return array<string, mixed>|null
     */
    private function metadata(string $id, string $apiKey): ?array
    {
        if ($apiKey === '') {
            return null;
        }

        $response = Http::timeout(15)->get(self::FILES_ENDPOINT.'/'.$id, [
            'key' => $apiKey,
            'fields' => 'id, name, mimeType, webViewLink',
            'supportsAllDrives' => 'true',
        ]);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Build a readable error for a failed Drive request.
     */
    private function errorMessage(Response $response): string
    {
        $status = $response->status();
        $reason = $response->json('error.errors.0.reason');

        return match (true) {
            $status === 404 || $reason === 'notFound' => __('That Google Drive folder could not be found. Make sure it is shared with “Anyone with the link”.'),
            $status === 403 || $reason === 'insufficientFilePermissions' => __('Google Drive refused access to that folder. Make sure it is shared with “Anyone with the link”.'),
            $status === 400 || $reason === 'badRequest' => __('Google Drive rejected the request. Check GOOGLE_DRIVE_API_KEY in your .env file.'),
            default => __('Google Drive returned an error (:status).', ['status' => $status]),
        };
    }

    /**
     * Build a Drive file link for a file ID.
     */
    private static function fileUrl(string $id): string
    {
        return 'https://drive.google.com/file/d/'.$id.'/view';
    }
}
