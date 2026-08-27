<?php

namespace App\Console\Commands;

use App\Models\Media;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportMegaS4Media extends Command
{
    protected $signature = 'media:import-s4
        {--video-limit=200 : Maximum browser-compatible videos to import}
        {--image-limit=500 : Maximum browser-compatible images to import}
        {--clean : Remove existing catalog entries before importing}';

    protected $description = 'Import browser-compatible private media from the configured MEGA S4 bucket';

    public function handle(): int
    {
        $disk = config('filesystems.disks.s3');
        $videoLimit = max(0, (int) $this->option('video-limit'));
        $imageLimit = max(0, (int) $this->option('image-limit'));

        if ($this->option('clean')) {
            Media::query()->delete();
        }

        $client = new S3Client([
            'version' => 'latest',
            'region' => $disk['region'],
            'endpoint' => $disk['endpoint'],
            'use_path_style_endpoint' => $disk['use_path_style_endpoint'],
            'credentials' => [
                'key' => $disk['key'],
                'secret' => $disk['secret'],
            ],
        ]);

        $mimeTypes = [
            'mp4' => 'video/mp4',
            'm4v' => 'video/x-m4v',
            'webm' => 'video/webm',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $counts = ['video' => 0, 'image' => 0];
        $scanned = 0;
        $paginator = $client->getPaginator('ListObjectsV2', ['Bucket' => $disk['bucket']]);

        foreach ($paginator as $page) {
            foreach ($page['Contents'] ?? [] as $object) {
                $scanned++;
                $key = (string) $object['Key'];
                $extension = strtolower(pathinfo($key, PATHINFO_EXTENSION));
                $mimeType = $mimeTypes[$extension] ?? null;

                if (! $mimeType) {
                    continue;
                }

                $kind = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
                $limit = $kind === 'video' ? $videoLimit : $imageLimit;

                if ($counts[$kind] >= $limit) {
                    continue;
                }

                $counts[$kind]++;
                $modifiedAt = isset($object['LastModified'])
                    ? Carbon::parse((string) $object['LastModified'])
                    : now();
                $label = $kind === 'video' ? 'Video' : 'Photo';
                $sequence = str_pad((string) $counts[$kind], 3, '0', STR_PAD_LEFT);

                Media::updateOrCreate(
                    ['s3_key' => $key],
                    [
                        'title' => "{$label} {$sequence} · {$modifiedAt->format('M j, Y')}",
                        'slug' => 'media-' . substr(hash('sha256', $key), 0, 20),
                        'description' => "Private {$label} stored in the MEGA S4 media vault.",
                        'mime_type' => $mimeType,
                        'duration' => 0,
                        'file_size' => (int) ($object['Size'] ?? 0),
                        'thumbnail_url' => null,
                        'is_active' => true,
                    ]
                );

                if ($counts['video'] >= $videoLimit && $counts['image'] >= $imageLimit) {
                    break 2;
                }
            }
        }

        $this->info("Imported {$counts['video']} videos and {$counts['image']} images after scanning {$scanned} objects.");

        return self::SUCCESS;
    }
}
