@php
    $tabs = menu()->getCurrentActiveMenu('admin')?->getChildren();
@endphp

@if (
    $tabs
    && $tabs->isNotEmpty()
)
    <div class="tabs">
        <div class="mb-4 flex flex-wrap gap-2 pt-2 max-sm:hidden">
            @foreach ($tabs as $tab)
                <a href="{{ $tab->getUrl() }}">
                    <div class="{{ $tab->isActive() ? 'brand-tab-active' : 'rounded-lg px-2.5 py-1.5 text-sm text-gray-600 dark:text-gray-300' }} cursor-pointer transition">
                        {{ $tab->getName() }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
