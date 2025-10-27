@extends('core.layouts.apps')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 g-xl-4">
  @include('focus.pages.home')
  @include('focus.pages.select-product')
  @include('focus.pages.delivery-details')
  @include('focus.pages.track')
</div>
@endsection
