<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-2xl border border-[#10233d] bg-[#10233d] px-5 py-3 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-sm transition duration-150 ease-in-out hover:bg-[#17395a] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/35 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
