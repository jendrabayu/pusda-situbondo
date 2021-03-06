@extends('layouts.admin-master2')

@section('title')
  RPJMD
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
            @include('admin.isiuraian.rpjmd.menu_tree')
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-9 pl-lg-2">
        @include('partials.alerts')
        <div class="card">
          <div class="card-body">
            <ul class="nav nav-pills" id="tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="table-tab" data-toggle="tab" href="#table" role="tab" aria-controls="table"
                  aria-selected="true">Tabel RPJMD</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="fitur-tab" data-toggle="tab" href="#fitur" role="tab" aria-controls="fitur"
                  aria-selected="false">Fitur RPJMD</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="file-tab" data-toggle="tab" href="#file" role="tab" aria-controls="file"
                  aria-controls="contact" aria-selected="false">File Pendukung RPJMD</a>
              </li>
            </ul>
            <div class="tab-content" id="tab-content">
              <div class="tab-pane fade show active" id="table" role="tabpanel" aria-labelledby="table-tab">
                <div class="d-flex justify-content-end align-items-center">
                  <button class="btn btn-success btn-icon icon-left mr-2" type="button" data-toggle="modal"
                    data-target="#modal-add-year">
                    <i class="fas fa-calendar-alt"></i> Pengaturan Tahun
                  </button>
                  @include('admin.isiuraian.partials.button_export', ['resource_name' => 'rpjmd', 'table_id' =>
                  $tabelRpjmd->id])
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
                      @foreach ($uraianRpjmd as $index => $uraian)
                        <tr>
                          <td class="text-center">
                            @if (is_null($uraian->parent_id))
                              {{ ++$index }}
                            @endif
                          </td>
                          <td><span class="text-danger font-weight-bold">{{ $uraian->uraian }}</span></td>
                          <td> {{ $uraian->satuan }} </td>
                          <td>{{ $uraian->ketersediaan_data_text }}</td>
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
                            <td>{{ $child->satuan }}</td>
                            <td>{{ $child->ketersediaan_data_text }}</td>
                            @foreach ($years as $y)
                              <th class="text-center">
                                {{ $child->isiRpjmd->where('tahun', $y)->first()->isi ?? 0 }}
                              </th>
                            @endforeach
                            <td class=" text-center">
                              <button data-id="{{ $child->id }}" class="btn btn-info btn-sm btn-grafik">Grafik</button>
                            </td>
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
                              <div class="btn-group btn-group-sm">
                                <button data-id="{{ $child->id }}" type="button" class="btn btn-warning btn-edit"><i
                                    class="fas fa-pencil-alt"></i></button>
                                <button data-id="{{ $child->id }}" type="button" class="btn btn-danger btn-delete"><i
                                    class="fas fa-trash-alt"></i></button>
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>


              <div class="tab-pane fade" id="fitur" role="tabpanel" aria-labelledby="fitur-tab">
                <form action="{{ route('admin.rpjmd.update_fitur', $fiturRpjmd->id) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="form-group">
                    <label>Deskripsi:</label>
                    <textarea name="deskripsi" class="form-control">{{ $fiturRpjmd->deskripsi }}</textarea>
                  </div>
                  <div class="form-group">
                    <label>Analisis:</label>
                    <textarea name="analisis" class="form-control">{{ $fiturRpjmd->analisis }}</textarea>
                  </div>
                  <div class="form-group">
                    <label>Permasalahan:</label>
                    <textarea name="permasalahan" class="form-control">{{ $fiturRpjmd->permasalahan }}</textarea>
                  </div>
                  <div class="form-group">
                    <label>Solusi atau Langkah-langkah Tindak Lanjut:</label>
                    <textarea name="solusi" class="form-control">{{ $fiturRpjmd->solusi }}</textarea>
                  </div>
                  <div class="form-group">
                    <label>Saran / Rekomendasi ke Gubernur atau Pusat:</label>
                    <textarea name="saran" class="form-control">{{ $fiturRpjmd->saran }}</textarea>
                  </div>
                  <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  </div>
                </form>
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
                            <div class="btn-group btn-group-sm">
                              <a href="{{ route('admin.rpjmd.files.download', $file->id) }}" class="btn btn-info">
                                <i class="fas fa-download"></i>
                              </a>
                              <button data-id="{{ $file->id }}" type="button" class="btn btn-danger btn-delete-file">
                                <i class="fas fa-trash-alt"></i>
                              </button>
                            </div>
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
  </section>
  @include('admin.isiuraian.partials.hidden_form')
@endsection

@section('outer')
  @include('admin.isiuraian.partials.modal_graphic')
  @include('admin.isiuraian.partials.modal_edit', ['action' => route('admin.rpjmd.update'), 'showKetersediaanData' =>
  true])
  @include('admin.isiuraian.partials.modal_file_upload', ['action' => route('admin.rpjmd.files.store', $tabelRpjmd->id) ])
  @include('admin.isiuraian.partials.modal_add_year', ['resource_name' => 'rpjmd', 'tabel_id' =>
  $tabelRpjmd->id])
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/isi-uraian.js') }}"></script>
  <script>
    $(function() {
      initIsiUraianPage('rpjmd', 'admin');
    });
  </script>
@endpush
