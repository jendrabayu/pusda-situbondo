<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserLogged;
use App\Http\Controllers\Controller;
use App\Models\FileIndikator;
use App\Models\FiturIndikator;
use App\Models\IsiIndikator;
use App\Models\TabelIndikator;
use App\Models\UraianIndikator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndikatorController extends Controller
{

    public function index(TabelIndikator $tabelIndikator = null)
    {
        $categories = TabelIndikator::all();

        if (is_null($tabelIndikator)) {
            return view('admin.isiuraian.indikator.index', compact('categories'));
        }

        $uraianIndikator = UraianIndikator::getUraianByTableId($tabelIndikator->id);
        $fiturIndikator = FiturIndikator::getFiturByTableId($tabelIndikator->id);
        $files = FileIndikator::where('tabel_indikator_id', $tabelIndikator->id)->get();
        $years = IsiIndikator::getYears();

        return view('admin.isiuraian.indikator.create', compact('tabelIndikator', 'categories', 'uraianIndikator',  'fiturIndikator', 'files', 'years'));
    }

    public function edit(Request $request, UraianIndikator $uraianIndikator)
    {
        abort_if(!$request->ajax(), 404);

        $isiIndikator = $uraianIndikator->isiIndikator()
            ->orderByDesc('tahun')
            ->groupBy('tahun')->take(5)
            ->get(['tahun', 'isi']);

        $response = [
            'uraian_id' => $uraianIndikator->id,
            'uraian_parent_id' => $uraianIndikator->parent_id,
            'uraian' => $uraianIndikator->uraian,
            'satuan' => $uraianIndikator->satuan,
            'isi' =>  $isiIndikator,
        ];

        return response()->json($response);
    }

    public function update(Request $request)
    {
        $uraianIndikator = UraianIndikator::findOrFail($request->uraian_id);

        $years = $uraianIndikator->isiIndikator()
            ->select('tahun')
            ->get()
            ->map(fn ($year) => $year->tahun);

        $rules = [
            'uraian' => ['required', 'string'],
            'satuan' => ['required', 'string'],
        ];

        $customMessages = [];

        foreach ($years as $year) {
            $key = 'tahun_' . $year;
            $rules[$key] = ['required', 'numeric'];
            $customMessages[$key . '.required'] = "Data tahun {$year} wajib diisi";
            $customMessages[$key . '.numeric'] = "Data tahun {$year} harus berupa angka";
        }

        $this->validate($request, $rules, $customMessages);

        $uraianIndikator->update([
            'uraian' => $request->uraian,
            'satuan' =>  $request->satuan,
        ]);

        $isiIndikator = IsiIndikator::where('uraian_indikator_id', $request->uraian_id)
            ->get()
            ->sortBy('tahun');

        foreach ($isiIndikator as $value) {
            $isi = IsiIndikator::find($value->id);
            $isi->isi = $request->get('tahun_' . $isi->tahun);
            $isi->save();
        }

        event(new UserLogged($request->user(), "Mengubah uraian  {$uraianIndikator->uraian}  Indikator"));
        return back()->with('alert-success', 'Isi uraian berhasil diupdate');
    }

    public function destroy(Request $request, UraianIndikator $uraianIndikator)
    {
        $uraianIndikator->delete();
        event(new UserLogged($request->user(), "Menghapus uraian  <i>{$uraianIndikator->uraian}</i>  Indikator"));
        return back()->with('alert-success', 'Isi uraian berhasil dihapus');
    }

    public function updateFitur(Request $request, FiturIndikator $fiturIndikator)
    {
        $validated = $this->validate($request, [
            'deskripsi' => ['nullable', 'string'],
            'analisis'  => ['nullable', 'string'],
            'permasalahan'  => ['nullable', 'string'],
            'solusi'  => ['nullable', 'string'],
            'saran'  => ['nullable', 'string']
        ]);

        $fiturIndikator->update($validated);
        event(new UserLogged($request->user(), "Mengubah fitur  <i>{$fiturIndikator}</i>  Indikator"));
        return back()->with('alert-success', 'Fitur berhasil diupdate');
    }

    public function storeFile(Request $request, TabelIndikator $tabelIndikator)
    {
        $request->validate([
            'file_document' => ['required', 'max:10000', 'mimes:pdf,doc,docx,xlsx,xls,csv'],
        ]);

        $file = $request->file('file_document');
        $fileName = (FileIndikator::latest()->first()->id ?? '') . $file->getClientOriginalName();
        $file->storeAs('file_pusda', $fileName, 'public');

        FileIndikator::create([
            'tabel_indikator_id' => $tabelIndikator->id,
            'file_name' =>  $fileName
        ]);
        event(new UserLogged($request->user(), "Menambah file pendukung  <i>{$fileName}</i>  pada menu  <i>{$tabelIndikator->menu_name}</i>  Indikator"));
        return back()->with('alert-success', 'File pendukung berhasil diupload');
    }

    public function destroyFile(Request $request, FileIndikator $fileIndikator)
    {
        Storage::delete('public/file_pusda/' . $fileIndikator->file_name);
        $fileIndikator->delete();
        event(new UserLogged($request->user(), "Menghapus file pendukung  <i>{$fileIndikator->file_name}</i>  Indikator"));
        return back()->with('alert-success', 'File pendukung berhasil dihapus');
    }

    public function downloadFile(Request $request, FileIndikator $fileIndikator)
    {
        return Storage::download('public/file_pusda/' . $fileIndikator->file_name);
        event(new UserLogged($request->user(), "Mendownload file pendukung  <i>{$fileIndikator->file_name}</i>  Indikator"));
    }

    public function storeTahun(Request $request, TabelIndikator $tabelIndikator)
    {
        abort_if(!$request->ajax(), 404);

        $request->validate(['tahun' => ['required', 'array']]);

        $tabelIndikator->uraianIndikator()->each(function ($uraian) use ($request) {
            foreach ($request->tahun as $tahun) {
                if (!is_null($uraian->parent_id)) {
                    $isiIndikator = IsiIndikator::where('uraian_indikator_id', $uraian->id)->where('tahun', $tahun)->first();
                    if (is_null($isiIndikator)) {
                        IsiIndikator::create([
                            'uraian_indikator_id' => $uraian->id,
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

    public function destroyTahun(Request $request, TabelIndikator $tabelIndikator, $year)
    {
        $uraianIndikator = $tabelIndikator->uraianIndikator;
        $uraianIndikator->each(function ($uraian) use ($year) {
            $uraian->isiIndikator()->where('tahun', $year)->delete();
        });

        event(new UserLogged($request->user(), 'Menghapus tahun tabel Indikator'));

        return back()->with('alert-success', 'Berhasil menghapus tahun');
    }
}
