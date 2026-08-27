<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Media::class);

        $search = trim((string) $request->query('search'));
        $type = $request->query('type');
        $mediaQuery = Media::query()->where('is_active', true);

        if ($search !== '') {
            $mediaQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (in_array($type, ['video', 'image'], true)) {
            $mediaQuery->where('mime_type', 'like', $type . '/%');
        }

        $mediaList = $mediaQuery->latest('id')->paginate(24)->withQueryString();
        $expiresAt = now()->addMinutes((int) config('filesystems.media_url_ttl_minutes', 15));

        $mediaList->getCollection()->each(function (Media $item) use ($expiresAt) {
            $item->preview_url = $item->isImage() ? $item->getTemporaryStreamUrl($expiresAt) : null;
        });

        return view('media.index', compact('mediaList', 'search', 'type'));
    }

    public function show(Request $request, Media $media): View
    {
        Gate::authorize('view', $media);

        $type = $request->query('type');
        $baseQuery = Media::query()->where('is_active', true);

        if (in_array($type, ['video', 'image'], true)) {
            $baseQuery->where('mime_type', 'like', $type . '/%');
        }

        // Carousel adjacent navigation
        $previousMedia = (clone $baseQuery)
            ->where('id', '>', $media->id)
            ->oldest('id')
            ->first();

        // Wrap around if at the end
        if (! $previousMedia) {
            $previousMedia = (clone $baseQuery)
                ->oldest('id')
                ->first();
        }

        $nextMedia = (clone $baseQuery)
            ->where('id', '<', $media->id)
            ->latest('id')
            ->first();

        // Wrap around if at the beginning
        if (! $nextMedia) {
            $nextMedia = (clone $baseQuery)
                ->latest('id')
                ->first();
        }

        $totalInScope = (clone $baseQuery)->count();
        $currentIndex = (clone $baseQuery)->where('id', '>=', $media->id)->count();

        return view('media.show', compact('media', 'previousMedia', 'nextMedia', 'totalInScope', 'currentIndex', 'type'));
    }

    public function stream(Media $media): JsonResponse
    {
        Gate::authorize('stream', $media);

        $ttlMinutes = (int) config('filesystems.media_url_ttl_minutes', 15);
        $expiresAt = now()->addMinutes($ttlMinutes);

        try {
            return response()->json([
                'status' => 'success',
                'media' => [
                    'id' => $media->id,
                    'title' => $media->title,
                    'slug' => $media->slug,
                    'kind' => $media->media_kind,
                    'mime_type' => $media->mime_type,
                    'duration' => $media->duration,
                    'file_size' => $media->file_size,
                    'formatted_duration' => $media->formatted_duration,
                    'formatted_file_size' => $media->formatted_file_size,
                ],
                'playback' => [
                    'stream_url' => $media->getTemporaryStreamUrl($expiresAt),
                    'expires_at' => $expiresAt->toIso8601String(),
                    'expires_in_seconds' => $ttlMinutes * 60,
                ],
            ], 200, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
                'Pragma' => 'no-cache',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to generate secure media access. Please try again.',
            ], 500);
        }
    }
}
