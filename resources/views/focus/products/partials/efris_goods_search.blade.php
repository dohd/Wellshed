@if (!$commodities->count())
    <div class="text-center text-danger h5">No matching commodity found!</div>
@else
    <ul class="list-unstyled">
        @foreach ($commodities as $key => $commodity)
            <li class="mb-1">
                @if ($key == 0)
                    <span class="tree-node level-0">▼ ({{ $commodity->family_code }}) {{ $commodity->segment_name }}</span>
                @else
                    <span class="tree-node level-0">▶ ({{ $commodity->family_code }}) {{ $commodity->segment_name }}</span>
                @endif
                <ul class="nested {{ $key == 0 ? 'active' : '' }}">
                    <li class="mb-1">
                        <span class="tree-node level-2">▼ ({{ $commodity->family_code }}) {{ $commodity->family_name }}</span>
                        <ul class="nested active">
                            <li class="mb-1">
                                <span class="tree-node level-2">▼ ({{ $commodity->class_code }}) {{ $commodity->class_name }}</span>
                                <ul class="nested active">
                                    <li class="mb-1">
                                        <span class="tree-node level" min_commodity_code="{{ $commodity->commodity_code }}">
                                            ({{ $commodity->commodity_code }}) {{ $commodity->commodity_name }}
                                        </span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        @endforeach
    </ul>
@endif
