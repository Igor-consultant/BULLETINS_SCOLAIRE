<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="i3p-kicker text-[#b02f25]">Administration</p>
                <h1 class="i3p-title mt-2">Parametres du bulletin</h1>
                <p class="mt-3 max-w-3xl text-[15px] leading-7 text-slate-600">
                    Modifie les regles de calcul, les appreciations, la sanction, le controle d acces financier et l en-tete institutionnel du bulletin.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8">
        @if (session('status'))
            <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                <div class="font-bold">Certains champs doivent etre corriges.</div>
            </div>
        @endif

        <form method="POST" action="{{ route('bulletins.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <section class="i3p-bulletin space-y-5">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">En-tete du bulletin</p>
                    <h2 class="i3p-section-title mt-2">Identite institutionnelle</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Nom de l'etablissement</span>
                        <input type="text" name="header[institution_name]" value="{{ old('header.institution_name', $settings['header']['institution_name']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Ligne contact</span>
                        <input type="text" name="header[contact_line]" value="{{ old('header.contact_line', $settings['header']['contact_line']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Ligne e-mail et ville</span>
                        <input type="text" name="header[email_line]" value="{{ old('header.email_line', $settings['header']['email_line']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Bloc droit titre</span>
                        <input type="text" name="header[republic_title]" value="{{ old('header.republic_title', $settings['header']['republic_title']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Bloc droit sous-titre</span>
                        <input type="text" name="header[republic_subtitle]" value="{{ old('header.republic_subtitle', $settings['header']['republic_subtitle']) }}" class="i3p-input">
                    </label>
                </div>
            </section>

            <section class="i3p-bulletin space-y-5">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">1. Calculs par matiere</p>
                    <h2 class="i3p-section-title mt-2">Ponderation</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Titre de section</span>
                        <input type="text" name="calculation[section_title]" value="{{ old('calculation.section_title', $settings['calculation']['section_title']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea name="calculation[section_description]" rows="3" class="i3p-input">{{ old('calculation.section_description', $settings['calculation']['section_description']) }}</textarea>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Poids note de classe</span>
                        <input type="number" step="0.01" min="0" name="calculation[devoir_weight]" value="{{ old('calculation.devoir_weight', $settings['calculation']['devoir_weight']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Poids composition</span>
                        <input type="number" step="0.01" min="0" name="calculation[composition_weight]" value="{{ old('calculation.composition_weight', $settings['calculation']['composition_weight']) }}" class="i3p-input">
                    </label>
                </div>
            </section>

            <section class="i3p-bulletin space-y-5">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">2. Calculs trimestriels</p>
                    <h2 class="i3p-section-title mt-2">Classement</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Titre de section</span>
                        <input type="text" name="trimestrial[section_title]" value="{{ old('trimestrial.section_title', $settings['trimestrial']['section_title']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea name="trimestrial[section_description]" rows="3" class="i3p-input">{{ old('trimestrial.section_description', $settings['trimestrial']['section_description']) }}</textarea>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Strategie ex aequo</span>
                        <select name="trimestrial[tie_break_strategy]" class="i3p-input">
                            <option value="alphabetique" @selected(old('trimestrial.tie_break_strategy', $settings['trimestrial']['tie_break_strategy']) === 'alphabetique')>Ordre alphabetique</option>
                        </select>
                    </label>
                </div>
            </section>

            @foreach ([
                'short_appreciations' => '3. Regles d appreciation par matiere',
                'general_appreciations' => '4. Regles d appreciation generale',
                'sanctions' => '5. Regles de sanction',
            ] as $key => $label)
                <section class="i3p-bulletin space-y-5">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">{{ $label }}</p>
                        <h2 class="i3p-section-title mt-2">Seuils configurables</h2>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-sm font-semibold text-slate-700">Titre de section</span>
                            <input type="text" name="{{ $key }}[section_title]" value="{{ old($key.'.section_title', $settings[$key]['section_title']) }}" class="i3p-input">
                        </label>
                        <label class="space-y-2 md:col-span-2">
                            <span class="text-sm font-semibold text-slate-700">Description</span>
                            <textarea name="{{ $key }}[section_description]" rows="3" class="i3p-input">{{ old($key.'.section_description', $settings[$key]['section_description']) }}</textarea>
                        </label>
                    </div>
                    <div class="overflow-x-auto rounded-3xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-slate-700">Min</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-700">Max</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-700">Libelle</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($settings[$key]['rules'] as $index => $rule)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" name="{{ $key }}[rules][{{ $index }}][min]" value="{{ old($key.'.rules.'.$index.'.min', $rule['min']) }}" class="i3p-input">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" name="{{ $key }}[rules][{{ $index }}][max]" value="{{ old($key.'.rules.'.$index.'.max', $rule['max']) }}" class="i3p-input">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="{{ $key }}[rules][{{ $index }}][label]" value="{{ old($key.'.rules.'.$index.'.label', $rule['label']) }}" class="i3p-input">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach

            <section class="i3p-bulletin space-y-5">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">6. Regles d exploitation dans l application</p>
                    <h2 class="i3p-section-title mt-2">Diffusion et acces</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-slate-700">Titre de section</span>
                        <input type="text" name="application[section_title]" value="{{ old('application.section_title', $settings['application']['section_title']) }}" class="i3p-input">
                    </label>
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-sm font-semibold text-slate-700">Description</span>
                        <textarea name="application[section_description]" rows="3" class="i3p-input">{{ old('application.section_description', $settings['application']['section_description']) }}</textarea>
                    </label>
                </div>
                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-semibold text-slate-700">
                    <input type="hidden" name="application[payment_gate_enabled]" value="0">
                    <input type="checkbox" name="application[payment_gate_enabled]" value="1" @checked(old('application.payment_gate_enabled', $settings['application']['payment_gate_enabled'])) class="h-5 w-5 rounded border-slate-300 text-[#0ca6e8] focus:ring-[#0ca6e8]/30">
                    Activer le controle d acces au bulletin selon le statut financier
                </label>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center rounded-2xl bg-[#10233d] px-6 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-[0_18px_40px_rgba(15,23,42,0.2)] transition hover:bg-[#17395a]">
                    Enregistrer les parametres
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
