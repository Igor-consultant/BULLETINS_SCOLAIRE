@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-[#0ca6e8] focus:outline-none focus:ring-4 focus:ring-[#0ca6e8]/12']) }}>
