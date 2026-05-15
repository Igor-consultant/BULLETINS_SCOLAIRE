<?php

namespace App\Http\Controllers;

use App\Services\BulletinSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulletinSettingController extends Controller
{
    public function __construct(
        private readonly BulletinSettingsService $settings,
    ) {
    }

    public function edit(): View
    {
        $this->ensureRoles('administration');

        return view('bulletins.settings', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureRoles('administration');

        $validated = $request->validate([
            'header.institution_name' => ['required', 'string', 'max:255'],
            'header.contact_line' => ['required', 'string', 'max:255'],
            'header.email_line' => ['required', 'string', 'max:255'],
            'header.republic_title' => ['required', 'string', 'max:255'],
            'header.republic_subtitle' => ['required', 'string', 'max:255'],
            'calculation.section_title' => ['required', 'string', 'max:255'],
            'calculation.section_description' => ['required', 'string', 'max:1000'],
            'calculation.devoir_weight' => ['required', 'numeric', 'min:0'],
            'calculation.composition_weight' => ['required', 'numeric', 'min:0'],
            'trimestrial.section_title' => ['required', 'string', 'max:255'],
            'trimestrial.section_description' => ['required', 'string', 'max:1000'],
            'trimestrial.tie_break_strategy' => ['required', 'in:alphabetique'],
            'short_appreciations.section_title' => ['required', 'string', 'max:255'],
            'short_appreciations.section_description' => ['required', 'string', 'max:1000'],
            'short_appreciations.rules' => ['required', 'array', 'min:1'],
            'short_appreciations.rules.*.min' => ['nullable', 'numeric'],
            'short_appreciations.rules.*.max' => ['nullable', 'numeric'],
            'short_appreciations.rules.*.label' => ['required', 'string', 'max:255'],
            'general_appreciations.section_title' => ['required', 'string', 'max:255'],
            'general_appreciations.section_description' => ['required', 'string', 'max:1000'],
            'general_appreciations.rules' => ['required', 'array', 'min:1'],
            'general_appreciations.rules.*.min' => ['nullable', 'numeric'],
            'general_appreciations.rules.*.max' => ['nullable', 'numeric'],
            'general_appreciations.rules.*.label' => ['required', 'string', 'max:500'],
            'sanctions.section_title' => ['required', 'string', 'max:255'],
            'sanctions.section_description' => ['required', 'string', 'max:1000'],
            'sanctions.rules' => ['required', 'array', 'min:1'],
            'sanctions.rules.*.min' => ['nullable', 'numeric'],
            'sanctions.rules.*.max' => ['nullable', 'numeric'],
            'sanctions.rules.*.label' => ['required', 'string', 'max:255'],
            'application.section_title' => ['required', 'string', 'max:255'],
            'application.section_description' => ['required', 'string', 'max:1000'],
            'application.payment_gate_enabled' => ['nullable', 'boolean'],
        ]);

        $validated['application']['payment_gate_enabled'] = $request->boolean('application.payment_gate_enabled');

        $this->settings->update($validated);

        return redirect()
            ->route('bulletins.settings.edit')
            ->with('status', 'Les parametres du bulletin ont ete mis a jour.');
    }
}
