<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'title',
        'slug',
        'description',
        's3_key',
        'mime_type',
        'duration',
        'file_size',
        'thumbnail_url',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'file_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getTemporaryStreamUrl(int|DateTimeInterface $expiration = 15, array $customOptions = []): string
    {
        $expiresAt = is_int($expiration) ? now()->addMinutes($expiration) : $expiration;
        $disk = Storage::disk('s3');

        if (! method_exists($disk, 'temporaryUrl')) {
            throw new RuntimeException('The configured media disk does not support private temporary URLs.');
        }

        return $disk->temporaryUrl($this->s3_key, $expiresAt, array_merge([
            'ResponseContentType' => $this->mime_type,
            'ResponseContentDisposition' => 'inline; filename="' . basename($this->s3_key) . '"',
        ], $customOptions));
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function mediaKind(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isVideo() ? 'video' : ($this->isImage() ? 'image' : 'file')
        );
    }

    public function formattedDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->duration <= 0) {
                    return '00:00';
                }

                $hours = floor($this->duration / 3600);
                $minutes = floor(($this->duration % 3600) / 60);
                $seconds = $this->duration % 60;

                return $hours > 0
                    ? sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds)
                    : sprintf('%02d:%02d', $minutes, $seconds);
            }
        );
    }

    public function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->file_size <= 0) {
                    return '0 B';
                }

                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $power = min((int) floor(log($this->file_size, 1024)), count($units) - 1);

                return round($this->file_size / (1024 ** $power), 2) . ' ' . $units[$power];
            }
        );
    }
}
