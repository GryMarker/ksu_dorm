@props([
    'striped' => true,
    'headers' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-ksu']) }}>
    <table class="min-w-full divide-y divide-slate-200">
        @if($headers)
            <thead class="bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-5 py-3">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @elseif (isset($head))
            <thead class="bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                {{ $head }}
            </thead>
        @endif
        <tbody class="text-sm text-slate-700 divide-y divide-slate-100 [&>tr]:transition-colors [&>tr:hover]:bg-ksu-100/40 {{ $striped ? '[&>tr:nth-child(even)]:bg-slate-50 [&>tr:nth-child(odd)]:bg-white' : '' }}">
            {{ $slot }}
        </tbody>
    </table>
</div>
