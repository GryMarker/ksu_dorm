@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data='{
        show: @js($show),
        dialog: null,
        focusables() {
            if (!this.dialog) return [];
            return Array.from(this.dialog.querySelectorAll("a, button, input:not([type=\"hidden\"]), textarea, select, [tabindex]:not([tabindex=\"-1\"])")).filter(el => !el.disabled);
        },
        focusNext() {
            const items = this.focusables();
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            (items[index + 1] || items[0]).focus();
        },
        focusPrev() {
            const items = this.focusables();
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            (items[index - 1] || items[items.length - 1]).focus();
        },
    }'
    x-init="$watch('show', value => {
        document.body.classList.toggle('overflow-y-hidden', value);
        if (value && dialog) { requestAnimationFrame(() => dialog.focus()); }
    })"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') { show = true }"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') { show = false }"
    x-on:keydown.escape.window="show = false"
>
    <template x-teleport="body">
        <div
            x-cloak
            x-show="show"
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
            style="display: none;"
        >
            <div
                class="fixed inset-0 transform transition-all"
                x-on:click="show = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="absolute inset-0 bg-ksu-900/70"></div>
            </div>

            <div
                x-show="show"
                x-ref="dialog"
                tabindex="-1"
                class="mb-6 transform overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-ksu transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                style="outline: none;"
                x-on:keydown.tab.prevent="focusNext()"
                x-on:keydown.shift.tab.prevent="focusPrev()"
            >
                <div x-init="dialog = $refs.dialog">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>
