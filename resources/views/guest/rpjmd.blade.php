@extends('guest.layouts.app')

@section('title')
  RPJMD
@endsection

@section('content')
  <div class="container">
    <div class="card">
      <div class="card-header bg-white card-header__lg">
        <h4 class="card-header__title">RPJMD</h4>
      </div>
      <div class="card-body">
        @include('guest.partials.menu-tree', ['categories' => $categories, 'resourceName' => 'rpjmd'])
      </div>
    </div>
  </div>
@endsection
