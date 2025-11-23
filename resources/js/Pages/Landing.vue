<template>
    <div class="min-h-screen bg-main text-primary">
        <!-- TOP NAV (simple) -->
        <header class="border-b border-main-border bg-main/95 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-accent font-bold text-main">
                        <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
                    </div>
                    <span class="text-sm font-semibold uppercase tracking-wide text-secondary">Spotacus</span>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <a href="#how-it-works" class="text-secondary hover:text-primary">How it works</a>
                    <a href="#features" class="text-secondary hover:text-primary">Features</a>
                    <a href="#preview" class="text-secondary hover:text-primary">Preview</a>

                    <!-- If user is logged in -->
                    <template v-if="user">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-lg bg-accent px-4 py-2 text-xs font-semibold uppercase tracking-wide text-main hover:bg-orange-hover"
                        >
                            My App
                        </Link>
                    </template>

                    <!-- If NOT logged in -->
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden rounded-lg border border-layer-border px-3 py-1 text-secondary hover:bg-layer-light md:inline-block"
                        >
                            Log in
                        </Link>

                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-accent px-4 py-2 text-xs font-semibold uppercase tracking-wide text-main hover:bg-orange-hover"
                        >
                            Try Spotacus
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- HERO -->
        <!-- HERO -->
        <section class="relative overflow-hidden bg-main">
            <!-- subtle glow -->
            <div
                class="pointer-events-none absolute -right-40 top-[-80px] h-[420px] w-[420px] rounded-full bg-accent/20 blur-3xl"
            ></div>
            <div
                class="relative mx-auto flex max-w-6xl flex-col items-center gap-12 px-6 pb-20 pt-16 md:flex-row md:items-center md:pt-24"
            >
                <!-- LEFT: TEXT -->
                <div class="flex-1 space-y-6">
                    <!-- badge / award style -->
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-layer-border bg-layer/70 px-3 py-1 text-xs uppercase tracking-wide text-secondary"
                    >
                        <span class="text-xs text-accent">RP-inspired hypertrophy</span>
                        <span class="h-1 w-1 rounded-full bg-layer-light"></span>
                        <span>Built for serious lifters</span>
                    </div>
                    <h1 class="text-4xl font-extrabold leading-tight md:text-5xl">
                        Effortless
                        <span class="text-accent">workout planning</span>
                        for hypertrophy.
                    </h1>
                    <p class="max-w-xl text-base text-secondary md:text-lg">
                        Spotacus turns your mesocycles, RIR targets, and recovery into a clear plan. No spreadsheets. No
                        overthinking. Just show up and lift.
                    </p>
                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center">
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center justify-center rounded-lg bg-accent px-7 py-3 text-sm font-semibold text-main hover:bg-orange-hover"
                        >
                            Start free mesocycle
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center justify-center rounded-lg border border-layer-border px-7 py-3 text-sm font-medium text-secondary hover:bg-layer-light"
                        >
                            View demo dashboard
                        </Link>
                    </div>
                    <!-- little trust row -->
                    <div class="flex flex-wrap items-center gap-4 pt-4 text-xs text-secondary">
                        <div class="flex items-center gap-1">
                            <span class="text-accent">★ ★ ★ ★ ★</span>
                            <span>Coach-approved structure</span>
                        </div>
                        <span class="hidden h-1 w-1 rounded-full bg-layer-light sm:inline-block"></span>
                        <span>Built around volume, fatigue & recovery</span>
                    </div>
                </div>
                <!-- RIGHT: PHONE STACK / PREVIEWS -->
                <div class="flex flex-1 justify-center md:justify-end">
                    <div class="relative w-[260px] md:w-[320px]">
                        <!-- back card -->
                        <div
                            class="absolute -left-6 top-6 h-[320px] w-[180px] rounded-[2rem] border border-layer-border/70 bg-layer opacity-70"
                        >
                            <!-- put a small screenshot or gradient here later -->
                        </div>
                        <!-- main phone -->
                        <div
                            class="relative flex h-[380px] flex-col overflow-hidden rounded-[2.5rem] border border-layer-border bg-layer-light shadow-[0_20px_60px_rgba(0,0,0,0.8)]"
                        >
                            <div class="flex h-8 items-center justify-center bg-main text-xs text-secondary">
                                <!-- fake notch -->
                                <div class="h-5 w-28 rounded-full border border-main-border bg-main/90"></div>
                            </div>
                            <!-- Replace this box with an <img> of your app -->
                            <div class="flex-1 bg-main/60 px-4 py-4 text-left text-xs text-secondary">
                                <p class="mb-2 text-[10px] uppercase tracking-wide text-accent">Today · Push Day</p>
                                <div class="space-y-2">
                                    <div class="rounded-lg bg-layer p-2">
                                        <p class="text-[11px] text-primary">Barbell Bench Press</p>
                                        <p class="text-[10px] text-secondary">4 sets · 6–8 reps · RIR 1</p>
                                    </div>
                                    <div class="rounded-lg bg-layer p-2">
                                        <p class="text-[11px] text-primary">Incline DB Press</p>
                                        <p class="text-[10px] text-secondary">3 sets · 8–10 reps · RIR 1–2</p>
                                    </div>
                                    <div class="rounded-lg bg-layer p-2">
                                        <p class="text-[11px] text-primary">Cable Fly</p>
                                        <p class="text-[10px] text-secondary">3 sets · 12–15 reps · RIR 2</p>
                                    </div>
                                </div>
                                <div class="mt-4 rounded-lg border border-layer-border bg-main/70 p-2">
                                    <p class="text-[10px] text-secondary">Weekly chest volume</p>
                                    <p class="text-[20px] font-bold text-accent">14 sets</p>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between border-t border-layer-border bg-main/90 px-4 py-3 text-[10px] text-secondary"
                            >
                                <span>Next: Pull · 2 days</span>
                                <span class="rounded-full bg-accent/10 px-2 py-1 text-[10px] text-accent">
                                    Auto-progression
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- STATS STRIP (under hero) -->
            <div class="border-t border-main-border bg-main">
                <div
                    class="mx-auto flex max-w-4xl flex-wrap items-center justify-center gap-8 px-6 py-6 text-center text-xs text-secondary md:text-sm"
                >
                    <div>
                        <div class="text-sm text-accent">Structured Training</div>
                        <div class="text-[11px] uppercase tracking-wide">Built for consistency</div>
                    </div>
                    <div class="h-8 w-px bg-layer-light/40"></div>
                    <div>
                        <div class="text-sm text-primary">Built for mesocycles</div>
                        <div class="text-[11px] uppercase tracking-wide">Not random workouts</div>
                    </div>
                    <div class="h-8 w-px bg-layer-light/40"></div>
                    <div>
                        <div class="text-sm text-primary">Volume-aware</div>
                        <div class="text-[11px] uppercase tracking-wide">Tracks fatigue & recovery</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section id="how-it-works" class="border-t border-main-border bg-layer px-6 py-24">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="mb-4 text-3xl font-bold">Take the guesswork out of strength training.</h2>
                <p class="mx-auto max-w-2xl text-secondary">
                    Spotacus helps you set clear mesocycles, manage fatigue, and know exactly when to push and when to
                    back off.
                </p>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="border-t border-main-border bg-main px-6 py-28">
            <div class="mx-auto max-w-6xl">
                <h2 class="mb-12 text-center text-3xl font-bold">Built for real hypertrophy training</h2>

                <div class="grid gap-10 md:grid-cols-3">
                    <!-- Feature 1 -->
                    <div
                        class="relative rounded-2xl border border-layer-border bg-layer-light p-8 transition duration-300 hover:border-accent/40 hover:bg-layer"
                    >
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 rounded-lg border-accent/20 p-4">
                            <img :src="plannerIcon" class="h-auto w-20" />
                        </div>

                        <h3 class="mt-6 text-xl font-semibold">Mesocycle Planner</h3>
                        <p class="mt-2 text-sm leading-relaxed text-secondary">
                            Build RP-style blocks, track sessions, and adjust your training volume across weeks.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="relative rounded-2xl border border-layer-border bg-layer-light p-8 transition duration-300 hover:border-accent/40 hover:bg-layer"
                    >
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 rounded-lg border-accent/20 p-4">
                            <img :src="recoveryIcon" alt="Recovery awareness icon" class="h-auto w-20" />
                        </div>

                        <h3 class="mt-6 text-xl font-semibold">Recovery Awareness</h3>
                        <p class="mt-2 text-sm leading-relaxed text-secondary">
                            Know exactly which muscle groups are ready to train based on weekly volume inputs.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="relative rounded-2xl border border-layer-border bg-layer-light p-8 transition duration-300 hover:border-accent/40 hover:bg-layer"
                    >
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 rounded-lg border-accent/20 p-4">
                            <img :src="autoProgressionIcon" alt="Auto-progression icon" class="h-auto w-20" />
                        </div>

                        <h3 class="mt-6 text-xl font-semibold">Auto-Progression</h3>
                        <p class="mt-2 text-sm leading-relaxed text-secondary">
                            Load or rep bumps recommended based on actual performance — without overthinking.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- PREVIEW SECTION WITH REAL SCREENSHOTS -->
        <section id="preview" class="border-t border-main-border bg-layer px-6 py-24">
            <div class="mx-auto grid max-w-6xl items-center gap-12 md:grid-cols-2">
                <!-- LEFT — Stylish Screenshots -->
                <div class="relative flex justify-center md:justify-start">
                    <!-- Screenshot 1 -->
                    <div
                        class="z-10 w-[220px] rotate-[-4deg] overflow-hidden rounded-xl border border-layer-border shadow-[0_20px_60px_rgba(0,0,0,0.5)] md:w-[260px]"
                    >
                        <img :src="mesoImg" alt="Spotacus workout view" class="h-auto w-full" />
                    </div>

                    <!-- Screenshot 2 -->
                    <div
                        class="absolute left-20 top-10 w-[220px] rotate-[6deg] overflow-hidden rounded-xl border border-layer-border opacity-90 shadow-[0_20px_60px_rgba(0,0,0,0.5)] md:w-[260px]"
                    >
                        <img :src="dashboardImg" alt="Spotacus dashboard" class="h-auto w-full" />
                    </div>
                </div>

                <!-- RIGHT — Text -->
                <div class="space-y-6 text-center md:text-left">
                    <p class="text-xs uppercase tracking-wide text-accent">Spotacus in Action</p>

                    <h2 class="text-3xl font-bold">See your training come to life.</h2>

                    <p class="max-w-lg text-base leading-relaxed text-secondary">
                        Visualize your weekly volume, plan mesocycles, track your progress, and follow auto-progression
                        tailored to your performance. Spotacus turns every workout into a clear step forward.
                    </p>

                    <Link
                        :href="route('register')"
                        class="inline-flex items-center rounded-lg bg-accent px-6 py-3 text-sm font-semibold text-main hover:bg-orange-hover"
                    >
                        Try Spotacus today
                    </Link>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section class="border-t border-main-border bg-main px-6 py-20">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="mb-4 text-3xl font-bold">Ready to try your next mesocycle in Spotacus?</h2>
                <p class="mx-auto mb-8 max-w-xl text-secondary">
                    Start with one block, track your volume and progress, and see if this fits your style of training.
                </p>
                <Link
                    :href="route('register')"
                    class="rounded-lg bg-accent px-10 py-4 font-semibold text-main hover:bg-orange-hover"
                >
                    Get started
                </Link>
            </div>
        </section>
    </div>
</template>

<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth?.user;

// adjust paths to where you actually put them
const dashboardImg = new URL("@/assets/images/landing/dashboard.webp", import.meta.url).href;
const mesoImg = new URL("@/assets/images/landing/meso.webp", import.meta.url).href;

const plannerIcon = new URL("@/assets/images/landing/planner.png", import.meta.url).href;
const recoveryIcon = new URL("@/assets/images/landing/recovery.png", import.meta.url).href;
const autoProgressionIcon = new URL("@/assets/images/landing/auto-progression.png", import.meta.url).href;

defineOptions({
    layout: DefaultLayout,
});
</script>
