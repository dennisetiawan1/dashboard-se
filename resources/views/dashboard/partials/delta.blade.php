@if(!is_null($delta))
    @php
        $cls = $delta > 0 ? 'badge-delta-up' : ($delta < 0 ? 'badge-delta-down' : 'badge-delta-flat');
        $sign = $delta > 0 ? '+' : '';
        $arrow = $delta > 0 ? '&uarr;' : ($delta < 0 ? '&darr;' : '&middot;');
    @endphp
    <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 w-fit {{ $cls }}">
        {!! $arrow !!} {{ $sign }}{{ number_format($delta) }} vs kemarin
    </span>
@endif
