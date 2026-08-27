<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="flex items-center gap-2 text-2xl font-bold leading-tight text-gray-800 dark:text-white">
                    <svg class="h-7 w-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                    </svg>
                    {{ __('Private Media Library') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Private MEGA S4 media delivered with short-lived signed URLs</p>
            </div>

            <form method="GET" action="{{ route('media.index') }}" class="flex w-full items-center sm:w-80">
                @if($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <div class="relative w-full">
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search media..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 ps-10 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center gap-2">
                @foreach(['' => 'All media', 'video' => 'Videos', 'image' => 'Photos'] as $filter => $label)
                    <a href="{{ route('media.index', array_filter(['type' => $filter, 'search' => $search])) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ ($type ?? '') === $filter ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="mb-8 flex items-center justify-between rounded-xl border border-indigo-500/20 bg-gradient-to-r from-indigo-900/30 to-slate-900/30 p-4 text-slate-300 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="rounded-lg bg-indigo-500/20 p-2 text-indigo-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-white">Private MEGA S4 access active</p>
                        <p class="text-xs text-slate-400">Every displayed image and video source uses an expiring V4 signature.</p>
                    </div>
                </div>
                <span class="hidden rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400 md:inline-flex">Encrypted</span>
            </div>

            @if($mediaList->isEmpty())
                <div class="rounded-2xl border border-gray-100 bg-white py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No media files found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adjust the search or media type filter.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($mediaList as $item)
                        <article class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                            <a href="{{ route('media.show', array_merge(['media' => $item->slug], array_filter(['type' => $type]))) }}" class="relative flex aspect-video items-center justify-center overflow-hidden bg-gray-950">
                                @if($item->isImage() && $item->preview_url)
                                    <img src="{{ $item->preview_url }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-tr from-slate-950 via-indigo-950 to-slate-900">
                                        <svg class="h-14 w-14 text-indigo-400/60 transition group-hover:scale-110 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute left-2.5 top-2.5 rounded-md bg-indigo-600/90 px-2 py-0.5 text-xs font-medium uppercase tracking-wider text-white backdrop-blur-sm">{{ $item->media_kind }}</span>
                                @if($item->isVideo() && $item->duration > 0)
                                    <span class="absolute bottom-2.5 right-2.5 rounded-md bg-black/75 px-2 py-0.5 text-xs font-semibold text-white">{{ $item->formatted_duration }}</span>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col justify-between p-5">
                                <div>
                                    <h3 class="line-clamp-1 text-base font-bold text-gray-900 transition group-hover:text-indigo-500 dark:text-white">
                                        <a href="{{ route('media.show', array_merge(['media' => $item->slug], array_filter(['type' => $type]))) }}">
                                            {{ $item->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-2 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-400 dark:border-gray-700/60">
                                    <span>{{ $item->formatted_file_size }}</span>
                                    <a href="{{ route('media.show', array_merge(['media' => $item->slug], array_filter(['type' => $type]))) }}" class="font-semibold text-indigo-500 hover:text-indigo-400 flex items-center gap-1">
                                        {{ $item->isVideo() ? 'Play' : 'View' }}
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">{{ $mediaList->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
