<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserLogged;
use App\Http\Controllers\Controller;
use App\Models\File8KelData;
use App\Models\Fitur8KelData;
use App\Models\Isi8KelData;
use App\Models\Skpd;
use App\Models\SkpdCategory;
use App\Models\Tabel8KelData;
use App\Models\Uraian8KelData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DelapanKelDataController extends Controller
{
    public function index($skpdCategory = null)
    {
        if ($skpdCategory) {
            $skpdCategory = SkpdCategory::where('name', $skpdCategory)->firstOrFail();
            $skpd = $skpdCategory->skpd;
            return view('admin.isiuraian.8keldata.category', compact('skpd', 'skpdCategory'));
        }

        $categories = Tabel8KelData::all();
        return view('admin.isiuraian.8keldata.index', compact('categories'));
    }

    public function input(Request $request, Tabel8KelData $tabel8KelData, Skpd $skpd = null)
    {
        $tabel8KelDataIds = null;
        $tabel8KelDataId = $tabel8KelData->id;

        if ($skpd) {
            $tabel8KelDataIds = $skpd->uraian8KelData()
                ->select('tabel_8keldata_id')
                ->groupBy('tabel_8keldata_id')
                ->get();
        }

        $categories = Tabel8KelData::all();
        $uraian8KelData = Uraian8KelData::getUraianByTableId($tabel8KelDataId);
        $fitur8KelData = Fitur8KelData::getFiturByTableId($tabel8KelDataId);
        $files = File8KelData::where('tabel_8keldata_id', $tabel8KelDataId)->get();
        $years = Isi8KelData::getYears($tabel8KelDataId);
        $allSkpd = Skpd::all()->pluck('singkatan', 'id');

        return view('admin.isiuraian.8keldata.input', compact('categories', 'skpd', 'tabel8KelData', 'uraian8KelData',  'fitur8KelData', 'files', 'years', 'allSkpd', 'tabel8KelDataIds'));
    }

    public function skpd(Skpd $skpd)
    {
        $tabel8KelDataIds = $skpd->uraian8KelData()
            ->select('tabel_8keldata_id')
            ->groupBy('tabel_8keldata_id')
            ->get();

        $categories = Tabel8KelData::all();

        return view('admin.isiuraian.8keldata.index', compact('categories', 'tabel8KelDataIds', 'skpd'));
    }

    public function edit(Request $request, Uraian8KelData $uraian8KelData)
    {
        abort_if(!$request->ajax(), 404);

        $isi8KelData = $uraian8KelData->isi8KelData()
            ->orderByDesc('tahun')
            ->groupBy('tahun')
            ->get(['tahun', 'isi']);

        $response = [
            'uraian_id' => $uraian8KelData->id,
            'uraian_parent_id' => $uraian8KelData->parent_id,
            'uraian' => $uraian8KelData->uraian,
            'satuan' => $uraian8KelData->satuan,
            'isi' => $isi8KelData,
            'ketersedian_data' => $uraian8KelData->ketersediaan_data
        ];

        return response()->json($response);
    }


    public function update(Request $request)
    {
        $uraian8KelData = Uraian8KelData::findOrFail($request->uraian_id);

        $years = $uraian8KelData->isi8KelData()
            ->select('tahun')
            ->get()
            ->map(fn ($year) => $year->tahun);

        $rules = [
            'uraian' => ['required', 'string'],
            'satuan' => ['required', 'string'],
            'ketersediaan_data' => ['required', 'integer'],
        ];

        $customMessages = [];

        foreach ($years as $year) {
            $key = 'tahun_' . $year;
            $rules[$key] = ['required', 'numeric'];
            $customMessages[$key . '.required'] = "Data tahun {$year} wajib diisi";
            $customMessages[$key . '.numeric'] = "Data tahun {$year} harus berupa angka";
        }

        $this->validate($request, $rules, $customMessages);

        $uraian8KelData->update([
            'uraian' => $request->uraian,
            'satuan' =>  $request->satuan,
            'ketersediaan_data' => $request->ketersediaan_data
        ]);

        $isi8KelData = Isi8KelData::where('uraian_8keldata_id', $request->uraian_id)
            ->get()
            ->sortBy('tahun');

        foreach ($isi8KelData as $value) {
            $isi = Isi8KelData::find($value->id);
            $isi->isi = $request->get('tahun_' . $isi->tahun);
            $isi->save();
        }

        event(new UserLogged($request->user(), 'Mengubah isi uraian tabel 8 kelompok data'));
        return back()->with('alert-success', 'Isi uraian berhasil diupdate');
    }

    public function destroy(Request $request, Uraian8KelData $uraian8KelData)
    {
        $uraian8KelData->delete();

        event(new UserLogged($request->user(), 'Menghapus uraian tabel 8 Kelompok Data'));

        return back()->with('alert-success', 'Isi uraian berhasil dihapus');
    }

    public function updateFitur(Request $request, Fitur8KelData $fitur8KelData)
    {
        $validated =    $this->validate($request, [
            'deskripsi' => ['nullable', 'string'],
            'analisis'  => ['nullable', 'string'],
            'permasalahan'  => ['nullable', 'string'],
            'solusi'  => ['nullable', 'string'],
            'saran'  => ['nullable', 'string']
        ]);

        $fitur8KelData->update($validated);

        event(new UserLogged($request->user(), 'Mengubah fitur tabel 8 kelompok data'));

        return back()->with('alert-success', 'Fitur berhasil diupdate');
    }

    public function storeFile(Request $request, Tabel8KelData $tabel8KelData)
    {
        $request->validate([
            'file_document' => ['required', 'max:10000', 'mimes:pdf,doc,docx,xlsx,xls,csv'],
        ]);

        $file = $request->file('file_document');
        $fileName = (File8KelData::latest()->first()->id ?? '') . $file->getClientOriginalName();
        $file->storeAs('file_pusda', $fileName, 'public');

        File8KelData::create([
            'tabel_8keldata_id' => $tabel8KelData->id,
            'file_name' =>  $fileName
        ]);

        event(new UserLogged($request->user(), 'Menambahkan file pendukung tabel 8 kelompok data'));

        return back()->with('alert-success', 'File pendukung berhasil diupload');
    }

    public function destroyFile(Request $request, File8KelData $file8KelData)
    {
        Storage::delete('public/file_pusda/' . $file8KelData->file_name);
        $file8KelData->delete();

        event(new UserLogged($request->user(), 'Menghapus file pendukung tabel 8 kelompok data'));

        return back()->with('alert-success', 'File pendukung berhasil dihapus');
    }

    public function downloadFile(Request $request, File8KelData $file8KelData)
    {
        event(new UserLogged($request->user(), 'Mengunduh file pendukung tabel 8 Kelompok Data'));

        return Storage::download('public/file_pusda/' . $file8KelData->file_name);
    }

    public function updateSumberData(Request $request, Uraian8KelData $uraian8KelData)
    {
        $request->validate(['sumber_data' => ['required', 'exists:skpd,id']]);
        $uraian8KelData->skpd_id = $request->sumber_data;
        $uraian8KelData->save();

        event(new UserLogged($request->user(), 'Mengubah sumber data uraian tabel 8 kelompok data'));

        return back()->with('alert-success', 'Sumber data isi uraian berhasil diupdate');
    }

    public function storeTahun(Request $request, Tabel8KelData $tabel8KelData)
    {
        abort_if(!$request->ajax(), 404);

        $request->validate(['tahun' => ['required', 'array']]);

        $tabel8KelData->uraian8KelData->each(function ($uraian) use ($request) {
            foreach ($request->tahun as $tahun) {
                if (!is_null($uraian->parent_id)) {
                    $isi8KelData = Isi8KelData::where('uraian_8keldata_id', $uraian->id)->where('tahun', $tahun)->first();
                    if (is_null($isi8KelData)) {
                        Isi8KelData::create([
                            'uraian_8keldata_id' => $uraian->id,
                            'tahun' => $tahun,
                            'isi' => 0
                        ]);
                    }
                }
            }
        });

        event(new UserLogged($request->user(), 'Menambahkan tahun tabel 8 kelompok data'));

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan tahun'
        ], 201);
    }

    public function destroyTahun(Request $request, Tabel8KelData $tabel8KelData, $year)
    {
        $uraian8KelData = $tabel8KelData->uraian8KelData;
        $uraian8KelData->each(function ($uraian) use ($year) {
            $uraian->isi8KelData()->where('tahun', $year)->delete();
        });

        event(new UserLogged($request->user(), 'Menghapus tahun tabel 8 kelompok data'));

        return back()->with('alert-success', 'Berhasil menghapus tahun');
    }
}
