@extends('layouts.admin-master2')

@section('title')
  8 Kelompok Data
@endsection

@section('content')
  <section class="section-body">
    <div class="row">
      <div class="col-12 col-lg-3 pr-lg-2">
        <div class="card">
          <div class="card-header">
            <h4 class="text-uppercase">Menu Tree View</h4>
          </div>
          <div class="card-body overflow-auto" id="jstree">
            @include('admin.isiuraian.8keldata.menu-tree')
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-9 pl-lg-2">
        @include('partials.alerts')
        <div class="card">
          <div class="card-body">

            <ul class="nav nav-tabs" id="tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="table-tab" data-toggle="tab" href="#table" role="tab" aria-controls="table"
                  aria-selected="true">Tabel 8 Kelompok Data</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="fitur-tab" data-toggle="tab" href="#fitur" role="tab" aria-controls="fitur"
                  aria-selected="false">Fitur 8 Kelompok Data</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="file-tab" data-toggle="tab" href="#file" role="tab" aria-controls="file"
                  aria-selected="false">File Pendukung 8 Kelompok Data</a>
              </li>
            </ul>
            <div class="tab-content tab-bordered" id="tab-content">
              <div class="tab-pane fade show active" id="table" role="tabpanel" aria-labelledby="tabel-tab">
                <div class="d-flex justify-content-end align-items-center">
                  <button class="btn btn-success btn-icon icon-left mr-2" type="button" data-toggle="modal"
                    data-target="#modalTahun">
                    <i class="fas fa-calendar-alt"></i> Pengaturan Tahun
                  </button>
                  @include('admin.isiuraian.partials.button-export', ['resource_name' => 'delapankeldata', 'table_id' =>
                  $tabel8KelData->id])
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover" id="isi-uraian-table">
                    <thead>
                      <tr>
                        <th class="text-center">No</th>
                        <th class="text-center text-danger">
                          Uraian
                        </th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Ketersedian Data</th>
                        @foreach ($years as $y)
                          <th class="text-center">
                            {{ $y }}
                          </th>
                        @endforeach
                        <th class="text-center">Grafik</th>
                        <th class="text-center">Sumber Data</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($uraian8KelData as $index => $uraian)
                        <tr>
                          <td class="text-center">
                            @if (is_null($uraian->parent_id))
                              {{ ++$index }}
                            @endif
                          </td>
                          <td><span class="text-danger font-weight-bold">{{ $uraian->uraian }}</span> </td>
                          <td>{{ $uraian->satuan }}</td>
                          <td>{{ $uraian->ketersediaan_data }}</td>
                          @foreach ($years as $y)
                            <th></th>
                          @endforeach
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @foreach ($uraian->childs as $child)
                          <tr>
                            <td></td>
                            <td><span class="text-danger d-block" style="text-indent: 1rem;">{{ $child->uraian }}</span>
                            </td>
                            <td>{{ $child->satuan }}</td>
                            <td>{{ $child->ketersediaan_data_text }}</td>
                            @foreach ($years as $y)
                              <th class="text-center">
                                {{ $child->isi8KelData->where('tahun', $y)->first()->isi }}
                              </th>
                            @endforeach
                            <td class="text-center"><button data-id="{{ $child->id }}"
                                class="btn btn-info btn-sm btn-grafik">Grafik</button></td>
                            <td>
                              <select name="sumber_data" class="form-control sumber-data" data-id="{{ $child->id }}">
                                <option value="none" selected disabled hidden></option>
                                @foreach ($allSkpd as $id => $singkatan)
                                  <option @if ($id === $child->skpd_id) selected @endif value="{{ $id }}">
                                    {{ $singkatan }}
                                  </option>
                                @endforeach
                              </select>
                            </td>
                            <td class="text-center">
                              <button data-id="{{ $child->id }}"
                                class="btn btn-icon btn-sm btn-warning m-1 btn-edit"><i class="fas fa-pencil-alt"></i>
                              </button>
                              <button data-id="{{ $child->id }}"
                                class="btn btn-icon btn-sm btn-danger m-1 btn-delete"><i class="fas fa-trash-alt"></i>
                              </button>
                            </td>
                          </tr>
                        @endforeach
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="fitur" role="tabpanel" aria-labelledby="fitur-tab">
                <div class="fitur-8keldata">
                  <form action="{{ route('admin.delapankeldata.update_fitur', $fitur8KelData->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                      <label>Deskripsi:</label>
                      <textarea name="deskripsi" class="form-control h-100"
                        rows="3">{{ $fitur8KelData->deskripsi }}</textarea>
                    </div>
                    <div class="form-group">
                      <label>Analisis:</label>
                      <textarea name="analisis" class="form-control h-100"
                        rows="3">{{ $fitur8KelData->analisis }}</textarea>
                    </div>
                    <div class="form-group">
                      <label>Permasalahan:</label>
                      <textarea name="permasalahan" class="form-control h-100"
                        rows="3">{{ $fitur8KelData->permasalahan }}</textarea>
                    </div>
                    <div class="form-group">
                      <label>Solusi atau Langkah-langkah Tindak Lanjut:</label>
                      <textarea name="solusi" class="form-control h-100"
                        rows="3">{{ $fitur8KelData->solusi }}</textarea>
                    </div>
                    <div class="form-group">
                      <label>Saran / Rekomendasi ke Gubernur atau Pusat:</label>
                      <textarea name="saran" class="form-control h-100" rows="3">{{ $fitur8KelData->saran }}</textarea>
                    </div>
                    <div class="form-group text-right"><button type="submit" class="btn btn-primary">Simpan
                        Perubahan</button></div>
                  </form>
                </div>
              </div>
              <div class="tab-pane fade" id="file" role="tabpanel" aria-labelledby="file-tab">
                <div class="d-flex justify-content-end align-items-center mb-3">
                  <button data-toggle="modal" data-target="#modal-file-upload" class="btn btn-success btn-icon icon-left">
                    <i class="fas fa-file-upload"></i>
                    Upload File
                  </button>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered" id="dataTable">
                    <thead>
                      <tr>
                        <th class="text-center">No</th>
                        <th>Nama File</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($files as $index => $file)
                        <tr>
                          <td class="text-center">{{ ++$index }}</td>
                          <td>{{ $file->file_name }}</td>
                          <td class="text-center">
                            <a href="{{ route('admin.delapankeldata.files.download', $file->id) }}"
                              class="btn btn-icon btn-sm btn-info m-1 btn-download-file">
                              <i class="fas fa-download"></i>
                            </a>
                            <button data-id="{{ $file->id }}"
                              class="btn btn-icon btn-sm btn-danger m-1 btn-delete-file">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @include('admin.isiuraian.partials.hidden-form')
    <form action="" method="post" id="deleteYearForm" hidden>
      @csrf
      @method('DELETE')
    </form>
  </section>
@endsection

@push('styles')
  @include('admin.isiuraian.partials.styles')
@endpush

@section('outer')
  @include('admin.isiuraian.partials.modal-graphic')
  @include('admin.isiuraian.partials.modal-edit', ['action' => route('admin.delapankeldata.update'),
  'showKetersediaanData' => true ])
  @include('admin.isiuraian.partials.modal-upload-file', ['action' => route('admin.delapankeldata.files.store',
  $tabel8KelData->id)
  ])
  @include('admin.isiuraian.partials.modal-year', ['action' => route('admin.delapankeldata.destroy_tahun', [$tabel8KelData->id, $years])])
@endsection

@push('scripts')
  @include('admin.isiuraian.partials.scripts')
  <script>
    $(function() {
      initIsiUraianPage('delapankeldata');

      $('#tahun').select2();

      $('#formTambahTahun').on('submit', function(e) {
        e.preventDefault();

        $('#modalTahun').modal('hide')
        Swal.fire({
          title: 'Mohon tunggu sebentar...',
          didOpen: () => {
            Swal.showLoading()
            $.ajax({
              url: $('#formTambahTahun').attr('action'),
              type: 'post',
              dataType: 'json',
              data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                tahun: $('select#tahun').val()
              },
              success: function(data) {
                if (data.success) {
                  Swal.fire({
                    title: data.message,
                    icon: 'success',
                    timer: 1000
                  })
                  window.location.reload();
                }
              },
              error: function(error) {
                Swal.fire({
                  title: 'Gagal menambahkan tahun',
                  text: error.responseJSON.message || error.statusText,
                  icon: 'error',
                  showConfirmButton: true,
                })
              }
            });
          },
          allowOutsideClick: () => !Swal.isLoading()
        })
      });

      $('.hapus-tahun').on('click', function(e) {
        const url = $(this).data('url');
        const form = $('#deleteYearForm');
        form.prop('action', url);
        console.log(url);
        Swal.fire({
          title: 'Apakah Anda Yakin?',
          text: 'Semua isi uraian pada tahun tersebut juga akan dihapus!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Batal',
          confirmButtonText: 'Hapus'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        })
      });
    });
  </script>
@endpush
