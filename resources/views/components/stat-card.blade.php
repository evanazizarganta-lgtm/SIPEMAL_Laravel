@props(['icon' => 'box', 'label', 'value', 'tone' => 'mint', 'href' => null, 'foot' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "stat tone-$tone rise"]) }}>
    <span class="stat-ico"><x-icon :name="$icon"/></span>
    <div>
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value">{{ $value }}</div>
    </div>
    @if($foot)<span class="stat-foot">{{ $foot }} @if($href)<x-icon name="arrow-right" class="icon-sm"/>@endif</span>@endif
</{{ $tag }}>
