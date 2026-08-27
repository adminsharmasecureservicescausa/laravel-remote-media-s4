<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default authorized demo user
        User::firstOrCreate(
            ['email' => 'admin@securestream.local'],
            [
                'name' => 'Security Admin',
                'password' => bcrypt('Password123!'),
                'email_verified_at' => now(),
            ]
        );

        $demoVideos = [
            [
                'title' => 'Cloud Infrastructure Security Masterclass 2026',
                'slug' => 'cloud-infrastructure-security-masterclass-2026',
                'description' => 'Comprehensive architecture deep-dive covering IAM least-privilege policies, S3 bucket lockdowns, VPC endpoint peering, and cryptographically verified streaming protocols.',
                's3_key' => 'catalog/training/infra-security-2026.mp4',
                'mime_type' => 'video/mp4',
                'duration' => 3840, // 1h 4m
                'file_size' => 784200000,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Zero-Trust Video Ingestion & HLS Packaging',
                'slug' => 'zero-trust-video-ingestion-and-hls-packaging',
                'description' => 'Hands-on architectural guide detailing how to ingest raw video streams, transcode into multi-bitrate HLS fragments, and generate expiring HMAC signatures for edge players.',
                's3_key' => 'catalog/hls/zero-trust-stream/master.m3u8',
                'mime_type' => 'application/x-mpegURL',
                'duration' => 1920, // 32m
                'file_size' => 452100000,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Real-Time Threat Detection & Rate-Limiting Pipelines',
                'slug' => 'real-time-threat-detection-and-rate-limiting',
                'description' => 'Exploration of layer-7 token throttling, distributed Redis token bucket algorithms, and mitigation strategies against video scraper bots and token sharing.',
                's3_key' => 'catalog/webinars/threat-mitigation.mp4',
                'mime_type' => 'video/mp4',
                'duration' => 2700, // 45m
                'file_size' => 610000000,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
            ],
            [
                'title' => 'Full-Stack Modern App Hardening with Laravel 11',
                'slug' => 'full-stack-modern-app-hardening-laravel-11',
                'description' => 'Security review of Laravel 11 middleware pipelines, Content Security Policy (CSP) headers, CORS configurations, and encrypted session handling.',
                's3_key' => 'catalog/courses/laravel11-hardening.mp4',
                'mime_type' => 'video/mp4',
                'duration' => 4500, // 1h 15m
                'file_size' => 950000000,
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80',
                'is_active' => true,
            ],
        ];

        foreach ($demoVideos as $video) {
            Media::updateOrCreate(['slug' => $video['slug']], $video);
        }
    }
}
