<a href="{{ $href }}" {{ $attributes->class(['btn btn-secondary' => ! $attributes->has('class')]) }} @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif>
    @if($denganIkon)
        @include('partials.icon', ['name' => 'arrow-left'])
    @endif
    {{ $slot->isNotEmpty() ? $slot : $label }}
</a>
