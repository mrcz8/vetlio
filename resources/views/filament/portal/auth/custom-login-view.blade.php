<div
    x-data="{
        slides: [
            {
                title: 'Need help?',
                text: 'Visit our Help Center to browse guides, video tutorials, and answers to the most common questions. ',
                link: '#',
                linkLabel: 'Open Help Center'
            },
            {
                title: 'Client Portal',
                text: 'Empower your clients with our self-service portal. They can view upcoming appointments, download invoices, and update their pet information anytime.',
                link: '#',
                linkLabel: 'Explore the Portal'
            },
            {
                title: 'Version History',
                text: 'Stay up to date with the latest improvements and bug fixes. Read the changelog to see what’s new in Vetlio.',
                link: '#',
                linkLabel: 'View Changelog'
            },
            {
                title: 'Forgot your password?',
                text: 'No worries! Use the password reset option on the login screen to create a new one within minutes. You’ll be back in no time.',
                link: '#',
                linkLabel: 'Reset Password'
            },
        ],
        active: 0,
        interval: null,

        next() { this.active = (this.active + 1) % this.slides.length },
        prev() { this.active = (this.active - 1 + this.slides.length) % this.slides.length },
        startAuto() { this.interval = setInterval(() => this.next(), 7000) },
        stopAuto() { clearInterval(this.interval); this.interval = null }
    }"
    x-init="startAuto()"
    class="flex flex-col items-center justify-center min-h-screen text-center bg-green-50 relative overflow-hidden border-r border-gray-200"
>
    <div class="max-w-md mb-10">
        <img
            src="{{ asset('login-background.png') }}"
            alt="Analytics Dashboard Illustration"
            class="w-full h-auto mx-auto drop-shadow-md select-none pointer-events-none"
        >
    </div>

    <template x-for="(slide, index) in slides" :key="index">
        <div
            x-show="active === index"
            x-transition.opacity
            class="absolute bottom-36 left-0 right-0 flex flex-col items-center justify-center px-6"
        >
            <h2 class="text-green-500 font-semibold text-base mb-2" x-text="slide.title"></h2>
            <p class="text-gray-700 font-medium text-sm mb-3 max-w-md" x-text="slide.text"></p>
            <template x-if="slide.link">
                <a :href="slide.link" class="text-green-500 font-semibold text-sm hover:underline" x-text="slide.linkLabel"></a>
            </template>
        </div>
    </template>

    <div class="absolute bottom-10 flex items-center justify-center space-x-6">
        <button
            @click="prev()"
            class="p-2 rounded-full bg-white/70 hover:bg-white shadow transition"
            aria-label="Previous"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <template x-for="(slide, index) in slides" :key="'dot-' + index">
            <button
                @click="active = index"
                :class="active === index ? 'bg-green-500 w-4' : 'bg-gray-300 w-2'"
                class="h-2 rounded-full transition-all duration-300"
                aria-label="Slide indicator"
            ></button>
        </template>

        <button
            @click="next()"
            class="p-2 rounded-full bg-white/70 hover:bg-white shadow transition"
            aria-label="Next"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
