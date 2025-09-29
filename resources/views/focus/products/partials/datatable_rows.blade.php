@foreach($products as $i => $product)
    @php $standard = $product->standard @endphp;
    <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $product->name }}</td>
        <td>{{ @$product->category->title ?: 'Uncategorized' }}</td>
        <td>{{ @$standard->code ?: 'Null-Code' }}</td>
        <td>{{ @$product->unit->code ?: 'Null-UoM' }}</td>
        <td>{{ $product->variations->sum('qty') }}</td>
        @if (access()->allow('product-view_purchase_price'))
            <td>{{ numberFormat($standard->purchase_price) }}</td>
        @endif
        <td>{{ @$standard->expiry? dateFormat($standard->expiry) : '' }}</td>
        <td>{!! $product->action_buttons !!}</td>
    </tr>
@endforeach
