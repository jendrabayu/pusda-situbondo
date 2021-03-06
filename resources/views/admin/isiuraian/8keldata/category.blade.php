@extends('layouts.admin-master')

@section('title')
  8 Kelompok Data
@endsection

@section('content')
  <section class="section-header">
    <h1 class="text-capitalize">8 Kelompok Data {{ $skpdCategory->name }}</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
      <div class="breadcrumb-item active"><a href="">8 Kel. Data</a></div>
      <div class="breadcrumb-item active"><span class="text-capitalize">{{ $skpdCategory->name }}</span></div>
    </div>
  </section>

  <section class="section-body">
    <div class="row">
      @foreach ($skpdCategory->skpd as $skpd)
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <div class="mb-2 text-dark"><i class="fas fa-tv" style="font-size: 2.5rem"></i></div>
              <p class="mb-0"><a class="stretched-link"
                  href="{{ route('admin.delapankeldata.skpd', $skpd->id) }}">{{ $skpd->singkatan }}</a>
              </p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>
@endsection
