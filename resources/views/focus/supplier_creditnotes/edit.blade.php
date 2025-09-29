@extends ('core.layouts.app')
@section('title', $is_debit ? 'Debit Notes Management' : 'Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $is_debit ? 'Edit Debit Note' : 'Edit Supplier Credit Note' }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.supplier_creditnotes.partials.supplier_creditnotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        {{ Form::model($creditnote, ['route' => ['biller.supplier_creditnotes.update', $creditnote], 'method' => 'PATCH']) }}
            @include('focus.supplier_creditnotes.form')
        {{ Form::close() }}
    </div>
</div>
@endsection