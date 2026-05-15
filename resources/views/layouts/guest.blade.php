<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="i3p-body text-slate-900 antialiased">
        <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto grid min-h-[calc(100vh-3rem)] w-full max-w-6xl overflow-hidden rounded-[2.25rem] border border-white/70 bg-white/70 shadow-[0_32px_120px_rgba(15,23,42,0.14)] backdrop-blur lg:grid-cols-[1.05fr_0.95fr]">
                <section class="relative overflow-hidden bg-[linear-gradient(145deg,#10233d_0%,#17395a_52%,#0ca6e8_160%)] px-8 py-10 text-white sm:px-10 lg:px-12 lg:py-14">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.16),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(176,47,37,0.22),transparent_22%)]"></div>
                    <div class="relative z-10 flex h-full flex-col justify-between gap-10">
                        <div class="space-y-8">
                            <a href="/" class="inline-flex items-center gap-4">
                                <x-application-logo class="h-20 w-auto rounded-2xl border border-white/15 bg-white/95 p-2 shadow-[0_18px_60px_rgba(15,23,42,0.22)]" />
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.32em] text-[#f0c5ba]">Institut Polytechnique Pierre Prie</p>
                                    <h1 class="mt-2 text-3xl font-bold tracking-[-0.03em]">BULLETINS SCOLAIRE</h1>
                                </div>
                            </a>

                            <div class="space-y-4">
                                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-100">
                                    Espace securise
                                </span>
                                <h2 class="max-w-xl text-4xl font-bold leading-tight tracking-[-0.04em]">
                                    Un poste de travail scolaire clair, pilote et digne d'une direction.
                                </h2>
                                <p class="max-w-xl text-[15px] leading-8 text-slate-200">
                                    Centralise les inscriptions, la saisie des notes, les resultats trimestriels,
                                    les bulletins, la comptabilite et le portail parent dans une interface plus lisible.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4">
                                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-200">Scolarite</div>
                                <div class="mt-2 text-sm leading-6 text-slate-100">Eleves, classes et inscriptions.</div>
                            </div>
                            <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4">
                                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-200">Resultats</div>
                                <div class="mt-2 text-sm leading-6 text-slate-100">Calculs, rangs, bulletins et archives.</div>
                            </div>
                            <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4">
                                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-200">Confiance</div>
                                <div class="mt-2 text-sm leading-6 text-slate-100">Acces controles par roles et suivi des actions.</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex items-center px-5 py-8 sm:px-8 lg:px-12">
                    <div class="mx-auto w-full max-w-md">
                        <div class="mb-6">
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#b02f25]">Connexion</p>
                            <h2 class="mt-3 text-3xl font-bold tracking-[-0.03em] text-slate-950">Acces a la plateforme</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Connecte-toi avec ton compte pour acceder a l'espace correspondant a ton role.
                            </p>
                        </div>

                        <div class="rounded-[2rem] border border-slate-200/80 bg-white px-6 py-6 shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
                            {{ $slot }}
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
