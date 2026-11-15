@props(['icon' => 'inbox', 'title', 'text' => null, 'label' => null, 'href' => null])
<div {{ $attributes->merge(['class' => 'empty']) }}>
    <div class="empty-art" aria-hidden="true"><span class="blob"></span><span class="blob2"></span><span class="ico"><x-icon :name="$icon"/></span></div>
    <h3>{{ $title }}</h3>
    @if($text)<p>{{ $text }}</p>@endif
    @if($label && $href)<a class="btn" href="{{ $href }}"><x-icon name="plus"/>{{ $label }}</a>@endif
    {{ $slot }}
</div>
