<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('media.index', array_filter(['type' => $type])) }}" class="rounded-lg bg-gray-100 p-2 text-gray-600 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Back to Media Library (Esc)">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="line-clamp-1 text-xl font-bold leading-tight text-gray-800 dark:text-white">{{ $media->title }}</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Private MEGA S4 {{ $media->media_kind }} · {{ $media->mime_type }}
                        @if($totalInScope > 1)
                            · <span class="font-semibold text-indigo-500">Item {{ $currentIndex }} of {{ $totalInScope }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($totalInScope > 1)
                    <div class="hidden sm:flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700/60 rounded-lg p-1 text-xs">
                        <a href="{{ route('media.show', array_merge(['media' => $previousMedia->slug], array_filter(['type' => $type]))) }}" class="p-1.5 rounded hover:bg-white dark:hover:bg-gray-600 text-gray-600 dark:text-gray-200 transition" title="Previous (Left Arrow Key)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <span class="px-2 font-mono font-medium text-gray-600 dark:text-gray-300">{{ $currentIndex }} / {{ $totalInScope }}</span>
                        <a href="{{ route('media.show', array_merge(['media' => $nextMedia->slug], array_filter(['type' => $type]))) }}" class="p-1.5 rounded hover:bg-white dark:hover:bg-gray-600 text-gray-600 dark:text-gray-200 transition" title="Next (Right Arrow Key)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endif
                <span class="hidden items-center rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400 md:inline-flex">V4 signed access</span>
            </div>
        </div>
    </x-slot>

    <div
        class="py-2 md:py-4"
        data-source-endpoint="{{ route('media.stream', $media) }}"
        data-media-kind="{{ $media->media_kind }}"
        data-mime-type="{{ $media->mime_type }}"
        data-prev-url="{{ $previousMedia ? route('media.show', array_merge(['media' => $previousMedia->slug], array_filter(['type' => $type]))) : '' }}"
        data-next-url="{{ $nextMedia ? route('media.show', array_merge(['media' => $nextMedia->slug], array_filter(['type' => $type]))) : '' }}"
        data-index-url="{{ route('media.index', array_filter(['type' => $type])) }}"
        x-data="secureMedia($el.dataset.sourceEndpoint, $el.dataset.mediaKind, $el.dataset.mimeType, $el.dataset.prevUrl, $el.dataset.nextUrl, $el.dataset.indexUrl)"
        @keydown.window="handleKeydown($event)"
    >
        <div class="mx-auto max-w-[100vw] px-2 sm:px-4 lg:px-6 space-y-6">

            <!-- Expansive Full-Window Theater Stage -->
            <div class="relative group flex items-center justify-center overflow-hidden rounded-2xl border border-gray-800/80 bg-black shadow-2xl w-full h-[78vh] md:h-[84vh] lg:h-[88vh]">
                
                <!-- Loading State -->
                <div x-show="loading" class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-slate-950/90 backdrop-blur-sm p-6 text-center">
                    <div class="relative">
                        <div class="h-16 w-16 animate-spin rounded-full border-4 border-indigo-500/20 border-t-indigo-500"></div>
                        <svg class="h-7 w-7 text-indigo-400 absolute inset-0 m-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-base font-semibold text-white">Loading private media...</p>
                    <p class="mt-1 text-xs text-slate-400">Secured via MEGA S4 V4 Pre-Signed Signature</p>
                </div>

                <!-- Error State -->
                <div x-show="errorMessage" x-cloak class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-slate-950/95 p-6 text-center">
                    <div class="p-3 bg-red-500/10 text-red-400 rounded-full border border-red-500/20 mb-3">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-xl font-bold text-white">Playback Access Notice</h3>
                    <p class="mt-1 max-w-md text-sm text-slate-300" x-text="errorMessage"></p>
                    <button @click="loadSource(true)" class="mt-5 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 shadow-lg shadow-indigo-600/30">
                        Retry Access
                    </button>
                </div>

                <!-- Floating Left Carousel Arrow -->
                @if($previousMedia)
                    <a
                        href="{{ route('media.show', array_merge(['media' => $previousMedia->slug], array_filter(['type' => $type]))) }}"
                        class="absolute left-3 md:left-6 z-20 flex items-center justify-center h-12 w-12 md:h-16 md:w-16 rounded-full bg-black/65 hover:bg-indigo-600 text-white/90 hover:text-white border border-white/15 hover:border-indigo-400 shadow-2xl backdrop-blur-md transition-all duration-300 transform -translate-y-1/2 top-1/2 hover:scale-110 active:scale-95 group/arrow focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        title="Previous: {{ $previousMedia->title }} (Left Arrow Key)"
                        aria-label="Previous Media"
                    >
                        <svg class="h-6 w-6 md:h-8 md:w-8 transition-transform group-hover/arrow:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                <!-- Floating Right Carousel Arrow -->
                @if($nextMedia)
                    <a
                        href="{{ route('media.show', array_merge(['media' => $nextMedia->slug], array_filter(['type' => $type]))) }}"
                        class="absolute right-3 md:right-6 z-20 flex items-center justify-center h-12 w-12 md:h-16 md:w-16 rounded-full bg-black/65 hover:bg-indigo-600 text-white/90 hover:text-white border border-white/15 hover:border-indigo-400 shadow-2xl backdrop-blur-md transition-all duration-300 transform -translate-y-1/2 top-1/2 hover:scale-110 active:scale-95 group/arrow focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        title="Next: {{ $nextMedia->title }} (Right Arrow Key)"
                        aria-label="Next Media"
                    >
                        <svg class="h-6 w-6 md:h-8 md:w-8 transition-transform group-hover/arrow:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif

                <!-- Expansive Media Surface (Image or Video) -->
                <div class="w-full h-full flex items-center justify-center bg-black overflow-hidden">
                    @if($media->isImage())
                        <img x-ref="image" alt="{{ $media->title }}" class="w-full h-full max-h-[78vh] md:max-h-[84vh] lg:max-h-[88vh] object-contain transition duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-black" id="video-theater-wrapper">
                            <video x-ref="video" playsinline controls preload="metadata" class="w-full h-full max-h-[78vh] md:max-h-[84vh] lg:max-h-[88vh] object-contain"></video>
                        </div>
                    @endif
                </div>

                <!-- Top Floating Info Badge -->
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-black/70 border border-white/15 rounded-full px-3.5 py-1 text-xs text-white/90 backdrop-blur-md shadow-lg">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium uppercase tracking-wider">{{ $media->media_kind }}</span>
                    @if($totalInScope > 1)
                        <span class="text-white/40">|</span>
                        <span class="font-mono text-indigo-300 font-semibold">{{ $currentIndex }} of {{ $totalInScope }}</span>
                    @endif
                </div>

                <!-- Keyboard Navigation Shortcut Hint -->
                <div class="hidden lg:flex absolute bottom-4 right-4 z-20 items-center gap-2 bg-black/70 border border-white/15 rounded-full px-3.5 py-1 text-[11px] text-white/80 backdrop-blur-md shadow-lg">
                    <span>Use <kbd class="px-1.5 py-0.5 rounded bg-white/15 text-white font-mono font-bold">←</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white/15 text-white font-mono font-bold">→</kbd> to browse · <kbd class="px-1.5 py-0.5 rounded bg-white/15 text-white font-mono font-bold">Esc</kbd> gallery</span>
                </div>
            </div>

            <!-- Media Details & Security Status -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $media->title }}</h1>
                        @if($totalInScope > 1)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('media.show', array_merge(['media' => $previousMedia->slug], array_filter(['type' => $type]))) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-600 transition shadow-sm">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Previous
                                </a>
                                <a href="{{ route('media.show', array_merge(['media' => $nextMedia->slug], array_filter(['type' => $type]))) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-600 transition shadow-sm">
                                    Next
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $media->description ?: 'Private media stored in MEGA S4.' }}</p>
                    
                    <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 pt-6 text-xs dark:border-gray-700 sm:grid-cols-4">
                        <div><dt class="font-semibold uppercase tracking-wider text-gray-400">Type</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">{{ ucfirst($media->media_kind) }}</dd></div>
                        <div><dt class="font-semibold uppercase tracking-wider text-gray-400">Size</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $media->formatted_file_size }}</dd></div>
                        <div><dt class="font-semibold uppercase tracking-wider text-gray-400">Format</dt><dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $media->mime_type }}</dd></div>
                        <div><dt class="font-semibold uppercase tracking-wider text-gray-400">Bucket Access</dt><dd class="mt-1 text-sm font-semibold text-emerald-500">Zero Public Access</dd></div>
                    </dl>
                </section>

                <aside class="flex flex-col justify-between rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-indigo-950 p-6 text-white shadow-sm">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Access Token
                            </h3>
                            <span class="rounded bg-indigo-500/20 px-2 py-0.5 font-mono text-xs text-indigo-300">15m TTL</span>
                        </div>
                        <div class="mt-4 rounded-xl border border-slate-700/60 bg-slate-800/80 p-3 text-xs">
                            <div class="flex justify-between"><span class="text-slate-400">Time remaining</span><span class="font-mono font-bold text-amber-400" x-text="timeRemaining"></span></div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-700"><div class="h-full bg-indigo-500 transition-all duration-1000" :style="`width: ${progress}%`"></div></div>
                        </div>
                        <p class="mt-3 text-xs leading-relaxed text-slate-400">Private streaming signature is generated on demand and renewed automatically prior to expiration.</p>
                    </div>
                    <button @click="loadSource(true)" :disabled="loading" class="mt-6 w-full rounded-lg border border-indigo-500/40 bg-indigo-600/30 px-3 py-2.5 text-xs font-semibold text-indigo-200 transition hover:bg-indigo-600/50 shadow-sm">
                        Renew Access
                    </button>
                </aside>
            </div>
        </div>
    </div>

    <script>
        function secureMedia(endpoint, kind, mimeType, prevUrl, nextUrl, indexUrl) {
            return {
                endpoint,
                kind,
                mimeType,
                prevUrl,
                nextUrl,
                indexUrl,
                loading: true,
                errorMessage: null,
                player: null,
                secondsRemaining: 0,
                totalSeconds: 900,
                timer: null,

                init() {
                    this.loadSource(false);
                },

                handleKeydown(event) {
                    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
                        return;
                    }

                    if (event.key === 'ArrowLeft' && this.prevUrl) {
                        event.preventDefault();
                        window.location.href = this.prevUrl;
                    } else if (event.key === 'ArrowRight' && this.nextUrl) {
                        event.preventDefault();
                        window.location.href = this.nextUrl;
                    } else if (event.key === 'Escape' && this.indexUrl) {
                        event.preventDefault();
                        window.location.href = this.indexUrl;
                    }
                },

                get timeRemaining() {
                    const minutes = Math.floor(this.secondsRemaining / 60);
                    const seconds = this.secondsRemaining % 60;
                    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                get progress() {
                    return this.totalSeconds > 0 ? Math.round((this.secondsRemaining / this.totalSeconds) * 100) : 0;
                },

                async loadSource(renewing) {
                    this.loading = !renewing;
                    this.errorMessage = null;

                    try {
                        const response = await fetch(this.endpoint, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error(response.status === 429 ? 'Too many access requests. Wait one minute and retry.' : `Access request failed with HTTP ${response.status}.`);
                        }

                        const payload = await response.json();
                        if (!payload.playback?.stream_url) {
                            throw new Error('The server returned an invalid media source.');
                        }

                        this.totalSeconds = payload.playback.expires_in_seconds || 900;
                        this.secondsRemaining = this.totalSeconds;
                        this.startTimer();
                        this.applySource(payload.playback.stream_url);
                    } catch (error) {
                        this.errorMessage = error.message || 'Unable to load this private media object.';
                    } finally {
                        this.loading = false;
                    }
                },

                applySource(url) {
                    if (this.kind === 'image') {
                        this.$refs.image.onerror = () => this.errorMessage = 'The image could not be decoded or its signed URL was rejected.';
                        this.$refs.image.src = url;
                        return;
                    }

                    const video = this.$refs.video;
                    const currentTime = Number.isFinite(video.currentTime) ? video.currentTime : 0;
                    const shouldResume = !video.paused && !video.ended;
                    video.onerror = () => this.errorMessage = 'This video format could not be played or MEGA S4 rejected the signed range request.';
                    video.src = url;
                    video.load();
                    video.addEventListener('loadedmetadata', () => {
                        if (currentTime > 0 && currentTime < video.duration) {
                            video.currentTime = currentTime;
                        }
                        if (shouldResume) {
                            video.play().catch(() => {});
                        }
                    }, { once: true });

                    if (!this.player) {
                        this.player = new window.Plyr(video, {
                            controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'fullscreen'],
                            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
                            tooltips: { controls: true, seek: true },
                            fullscreen: { enabled: true, fallback: true, iosNative: true }
                        });
                    }
                },

                startTimer() {
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.secondsRemaining = Math.max(0, this.secondsRemaining - 1);
                        if (this.secondsRemaining === 60) {
                            this.loadSource(true);
                        }
                    }, 1000);
                }
            };
        }
    </script>
</x-app-layout>
