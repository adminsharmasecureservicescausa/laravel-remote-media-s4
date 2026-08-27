<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStreamingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_cannot_view_media_gallery(): void
    {
        $response = $this->get(route('media.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_media_gallery(): void
    {
        $media = Media::factory()->create([
            'title' => 'Laravel Security Masterclass',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('media.index'));

        $response->assertOk();
        $response->assertSee('Laravel Security Masterclass');
    }

    public function test_authenticated_user_can_filter_and_view_private_images(): void
    {
        Storage::fake('s3');

        Media::factory()->create([
            'title' => 'Private Vault Photo',
            's3_key' => 'photos/private/photo.jpg',
            'mime_type' => 'image/jpeg',
            'is_active' => true,
        ]);

        Media::factory()->create([
            'title' => 'Filtered Video',
            'mime_type' => 'video/mp4',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('media.index', ['type' => 'image']));

        $response->assertOk();
        $response->assertSee('Private Vault Photo');
        $response->assertDontSee('Filtered Video');
    }

    public function test_guest_cannot_request_signed_streaming_url(): void
    {
        $media = Media::factory()->create();

        $response = $this->getJson(route('media.stream', $media));

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_receives_valid_presigned_metadata(): void
    {
        Storage::fake('s3');

        $media = Media::factory()->create([
            'title' => 'Encrypted Video Asset',
            's3_key' => 'videos/secure/sample.mp4',
            'mime_type' => 'video/mp4',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('media.stream', $media));

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'media' => [
                'id',
                'title',
                'slug',
                'kind',
                'mime_type',
                'duration',
                'file_size',
                'formatted_duration',
                'formatted_file_size',
            ],
            'playback' => [
                'stream_url',
                'expires_at',
                'expires_in_seconds',
            ],
        ]);

        $response->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_inactive_media_streaming_is_forbidden_by_policy(): void
    {
        $media = Media::factory()->inactive()->create([
            'title' => 'Disabled Media Asset',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('media.stream', $media));

        $response->assertForbidden();
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->actingAs($this->user)->get(route('media.index'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
    }
}
