<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserLogged;
use App\Http\Controllers\Controller;
use App\Models\FileRpjmd;
use App\Models\FiturRpjmd;
use App\Models\IsiRpjmd;
use App\Models\Skpd;
use App\Models\SkpdCategory;
use App\Models\TabelRpjmd;
use App\Models\UraianRpjmd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RpjmdController extends Controller
{
    public function category(Request $request, $categoryName)
    {
        $skpdCategory = SkpdCategory::where('name', $categoryName)->firstOrFail();
        $skpd =  $skpdCategory->skpd()->get();
        return view('admin.isiuraian.rpjmd.category', compact('skpd', 'skpdCategory'));
    }

    public function index(Request $request, TabelRpjmd $tabelRpjmd = null)
    {
        $categories = TabelRpjmd::all();

        $skpdCategory = SkpdCategory::where('name', $request->input('category'))->first();
        $skpdIds =  $skpdCategory ? $skpdCategory->skpd()->get('id') : null;

        if (is_null($tabelRpjmd)) {
            return view('admin.isiuraian.rpjmd.index', compact('categories', 'skpdIds'));
        }

        $uraianRpjmd = UraianRpjmd::getUraianByTableId($tabelRpjmd->id);
        $fiturRpjmd = FiturRpjmd::getFiturByTableId($tabelRpjmd->id);
        $files = FileRpjmd::where('tabel_rpjmd_id', $tabelRpjmd->id)->get();
        $years = IsiRpjmd::getYears();
        $allSkpd = Skpd::all()->pluck('singkatan', 'id');

        return view('admin.isiuraian.rpjmd.create', compact('tabelRpjmd', 'categories', 'uraianRpjmd',  'fiturRpjmd', 'files', 'years', 'allSkpd', 'skpdIds'));
    }

    public function edit(Request $request, UraianRpjmd $uraianRpjmd)
    {
        abort_if(!$request->ajax(), 404);

        $isiRpjmd = $uraianRpjmd->isiRpjmd()
            ->orderByDesc('tahun')
            ->groupBy('tahun')
            ->get(['tahun', 'isi']);

        $response = [
            'uraian_id' => $uraianRpjmd->id,
            'uraian_parent_id' => $uraianRpjmd->parent_id,
            'uraian' => $uraianRpjmd->uraian,
            'satuan' => $uraianRpjmd->satuan,
            'isi' =>  $isiRpjmd,
            'ketersedian_data' => $uraianRpjmd->ketersediaan_data
        ];

        return response()->json($response);
    }

    public function update(Request $request)
    {
        $uraianRpjmd = UraianRpjmd::findOrFail($request->uraian_id);

        $years = $uraianRpjmd->isiRpjmd()
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

        $uraianRpjmd->update([
            'uraian' => $request->uraian,
            'satuan' =>  $request->satuan,
            'ketersediaan_data' => $request->ketersediaan_data
        ]);

        $isiRpjmd = IsiRpjmd::where('uraian_8keldata_id', $request->uraian_id)
            ->get()
            ->sortBy('tahun');

        foreach ($isiRpjmd as $value) {
            $isi = IsiRpjmd::find($value->id);
            $isi->isi = $request->get('tahun_' . $isi->tahun);
            $isi->save();
        }

        event(new UserLogged($request->user(), 'Mengubah isi uraian tabel RPJMD'));
        return back()->with('alert-success', 'Isi uraian berhasil diupdate');
    }

    public function destroy(Request $request, UraianRpjmd $uraianRpjmd)
    {
        $uraianRpjmd->delete();
        event(new UserLogged($request->user(), "Menghapus uraian  <i>{$uraianRpjmd->uraian}</i>  RPJMD"));
        return back()->with('alert-success', 'Isi uraian berhasil dihapus');
    }

    public function updateFitur(Request $request, FiturRpjmd $fiturRpjmd)
    {
        $validated =    $this->validate($request, [
            'deskripsi' => ['nullable', 'string'],
            'analisis'  => ['nullable', 'string'],
            'permasalahan'  => ['nullable', 'string'],
            'solusi'  => ['nullable', 'string'],
            'saran'  => ['nullable', 'string']
        ]);

        $fiturRpjmd->update($validated);
        event(new UserLogged($request->user(), "Mengubah fitur  <i>{$fiturRpjmd}</i>  RPJMD"));
        return back()->with('alert-success', 'Fitur berhasil diupdate');
    }

    public function storeFile(Request $request, TabelRpjmd $tabelRpjmd)
    {
        $request->validate([
            'file_document' => ['required', 'max:10000', 'mimes:pdf,doc,docx,xlsx,xls,csv'],
        ]);

        $file = $request->file('file_document');
        $fileName = (FileRpjmd::latest()->first()->id ?? '') . $file->getClientOriginalName();
        $file->storeAs('file_pusda', $fileName, 'public');

        FileRpjmd::create([
            'tabel_rpjmd_id' => $tabelRpjmd->id,
            'file_name' =>  $fileName
        ]);
        event(new UserLogged($request->user(), "Menambah file pendukung  <i>{$fileName}</i>  pada menu  <i>{$tabelRpjmd->menu_name}</i>  RPJMD"));
        return back()->with('alert-success', 'File pendukung berhasil diupdate');
    }

    public function destroyFile(Request $request, FileRpjmd $fileRpjmd)
    {
        Storage::delete('public/file_pusda/' . $fileRpjmd->file_name);
        $fileRpjmd->delete();
        event(new UserLogged($request->user(), "Menghapus file pendukung  <i>{$fileRpjmd->file_name}</i>  RPJMD"));
        return back()->with('alert-success', 'File pendukung berhasil dihapus');
    }

    public function downloadFile(Request $request, FileRpjmd $fileRpjmd)
    {
        return Storage::download('public/file_pusda/' . $fileRpjmd->file_name);
        event(new UserLogged($request->user(), "Mendownload file pendukung  <i>{$fileRpjmd->file_name}</i>  RPJMD"));
    }

    public function updateSumberData(Request $request, UraianRpjmd $uraianRpjmd)
    {
        $request->validate(['sumber_data' => ['required', 'exists:skpd,id']]);

        $uraianRpjmd->skpd_id = $request->sumber_data;
        $uraianRpjmd->save();
        event(new UserLogged($request->user(), "Merubah sumber data pada uraian  <i>{$uraianRpjmd->uraian}</i>  RPJMD"));
        return back()->with('alert-success', 'Sumber data isi uraian berhasil diupdate');
    }

    public function storeTahun(Request $request, TabelRpjmd $tabelRpjmd)
    {
        abort_if(!$request->ajax(), 404);

        $request->validate(['tahun' => ['required', 'array']]);

        $tabelRpjmd->uraianRpmd()->each(function ($uraian) use ($request) {
            foreach ($request->tahun as $tahun) {
                if (!is_null($uraian->parent_id)) {
                    $isiRpjmd = IsiRpjmd::where('uraian_rpjmd_id', $uraian->id)->where('tahun', $tahun)->first();
                    if (is_null($isiRpjmd)) {
                        IsiRpjmd::create([
                            'uraian_rpjmd_id' => $uraian->id,
                            'tahun' => $tahun,
                            'isi' => 0
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan tahun'
        ], 201);
    }

    public function destroyTahun(Request $request, TabelRpjmd $tabelRpjmd, $year)
    {
        $uraianRpjmd = $tabelRpjmd->uraianRpjmd;
        $uraianRpjmd->each(function ($uraian) use ($year) {
            $uraian->isiRpjmd()->where('tahun', $year)->delete();
        });

        event(new UserLogged($request->user(), 'Menghapus tahun tabel RPJMD'));

        return back()->with('alert-success', 'Berhasil menghapus tahun');
    }
}
