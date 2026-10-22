@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'left',
])

<div @class(['max-w-2xl', 'mx-auto text-center' => $align === 'center'])>
    @if ($eyebrow)
        <p class="label-xs flex items-center gap-3 text-flame {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="h-px w-8 bg-flame"></span>
            {{ $eyebrow }}
        </p>
    @endif

    <h2 class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-balance sm:text-4xl lg:text-[2.75rem] lg:leading-[1.05]">
        {{ $title }}
    </h2>

    @if ($description)
        <p class="mt-4 text-base leading-relaxed text-ink/55">{{ $description }}</p>
    @endif
</div>
