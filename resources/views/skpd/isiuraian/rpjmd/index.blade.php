@extends('layouts.admin-master3')

@section('title')
  RPJMD
@endsection

@section('content')
  <section class="section-body">
    <div class="card">
      <div class="card-header">
        <h4 class=" text-uppercase">Pilih Menu Tree View</h4>
      </div>
      <div class="card-body overflow-auto" id="treeview">
        @include('skpd.isiuraian.rpjmd.menu-tree')
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  @include('skpd.isiuraian.partials.scripts')
  <script>
    $(function() {
      initTreeView(true, '#treeview');
    });
  </script>
@endpush