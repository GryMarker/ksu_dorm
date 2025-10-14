@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-ksu-600 focus:ring-ksu-400 rounded-xl shadow-sm']) }}>
