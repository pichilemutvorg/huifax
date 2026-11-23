{{-- Inline styles: the panel's stylesheet only ships Filament's own classes. --}}
<x-filament-panels::page>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem">
        <div style="min-width: 18rem">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="theme" :aria-label="__('admin.preview.theme')">
                    @foreach ($this->themeOptions() as $value => $label)
                        <option value="{{ $value }}">{{ __('admin.preview.theme') }}: {{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <x-filament::button color="gray" icon="heroicon-o-arrow-path" wire:click="refresh">
            {{ __('admin.preview.refresh') }}
        </x-filament::button>

        <p class="fi-section-header-description" style="margin: 0">{{ __('admin.preview.help') }}</p>
    </div>

    <iframe
        wire:key="{{ $this->frameUrl() }}"
        src="{{ $this->frameUrl() }}"
        title="{{ __('admin.preview.title') }}"
        style="
            display: block;
            width: 100%;
            height: calc(100vh - 14rem);
            min-height: 32rem;
            border: 0;
            border-radius: 0.75rem;
        "
    ></iframe>
</x-filament-panels::page>
