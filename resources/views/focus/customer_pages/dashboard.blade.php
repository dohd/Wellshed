@extends('core.layouts.apps')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 g-xl-4">
  @include('focus.customer_pages.home')
  @include('focus.customer_pages.select-product')
  @include('focus.customer_pages.delivery-details')
  @include('focus.customer_pages.track')
</div>
@endsection
