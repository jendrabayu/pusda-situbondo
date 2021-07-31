<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserLogged;
use App\Http\Controllers\Controller;
use App\Models\FileBps;
use App\Models\FiturBps;
use App\Models\IsiBps;
use App\Models\TabelBps;
use App\Models\UraianBps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BpsController extends Controller
{
    public function index(TabelBps $tabelBps = null)
    {
        $categories = TabelBps::all();

        if (is_null($tabelBps)) {
            return view('admin.isiuraian.bps.index', compact('categories'));
        }

        $uraianBps = UraianBps::getUraianByTableId($tabelBps->id);
        $fiturBps = FiturBps::getFiturByTableId($tabelBps->id);
        $files = FileBps::where('tabel_bps_id', $tabelBps->id)->get();
        $years = IsiBps::getYears();

        return view('admin.isiuraian.bps.create', compact('tabelBps', 'categories', 'uraianBps',  'fiturBps', 'files', 'years'));
    }

    public function edit(Request $request, UraianBps $uraianBps)
    {
        abort_if(!$request->ajax(), 404);

        $isiBps = $uraianBps->isiBps()
            ->orderByDesc('tahun')
            ->groupBy('tahun')
            ->get(['tahun', 'isi']);

        $response = [
            'uraian_id' => $uraianBps->id,
            'uraian_parent_id' => $uraianBps->parent_id,
            'uraian' => $uraianBps->uraian,
            'satuan' => $uraianBps->satuan,
            'isi' =>  $isiBps,
        ];

        return response()->json($response);
    }

    public function update(Request $request)
    {
        $uraianBps = UraianBps::findOrFail($request->uraian_id);

        $years = $uraianBps->isiBps()
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

        $uraianBps->update([
            'uraian' => $request->uraian,
            'satuan' =>  $request->satuan,
        ]);

        $isiBps = IsiBps::where('uraian_bps_id', $request->uraian_id)
            ->get()
            ->sortBy('tahun');

        foreach ($isiBps as $value) {
            $isi = IsiBps::find($value->id);
            $isi->isi = $request->get('tahun_' . $isi->tahun);
            $isi->save();
        }

        event(new UserLogged($request->user(), "Mengubah uraian  <i>{$uraianBps->uraian}</i>  BPS"));
        return back()->with('alert-success', 'Isi uraian berhasil diupdate');
    }

    public function destroy(Request $request, UraianBps $uraianBps)
    {
        $uraianBps->delete();
        event(new UserLogged($request->user(), "Menghapus uraian  <i>{$uraianBps->uraian}</i>  BPS"));
        return back()->with('alert-success', 'Isi uraian berhasil dihapus');
    }

    public function updateFitur(Request $request, FiturBps $fiturBps)
    {
        $validated = $this->validate($request, [
            'deskripsi' => ['nullable', 'string'],
            'analisis'  => ['nullable', 'string'],
            'permasalahan'  => ['nullable', 'string'],
            'solusi'  => ['nullable', 'string'],
            'saran'  => ['nullable', 'string']
        ]);

        $fiturBps->update($validated);
        event(new UserLogged($request->user(), "Mengubah fitur  <i>{$fiturBps}</i>  BPS"));
        return back()->with('alert-success', 'Fitur berhasil diupdate');
    }


    public function storeFile(Request $request, TabelBps $tabelBps)
    {
        $request->validate([
            'file_document' => ['required', 'max:10000', 'mimes:pdf,doc,docx,xlsx,xls,csv'],
        ]);

        $file = $request->file('file_document');
        $fileName = (FileBps::latest()->first()->id ?? '') . $file->getClientOriginalName();
        $file->storeAs('file_pusda', $fileName, 'public');

        FileBps::create([
            'tabel_bps_id' => $tabelBps->id,
            'file_name' =>  $fileName
        ]);
        event(new UserLogged($request->user(), "Menambah file pendukung  <i>{$fileName}</i>  pada menu  <i>{$tabelBps->menu_name}</i>  BPS"));
        return back()->with('alert-success', 'File pendukung berhasil diupload');
    }

    public function destroyFile(Request $request, FileBps $fileBps)
    {
        Storage::delete('public/file_pusda/' . $fileBps->file_name);
        $fileBps->delete();
        event(new UserLogged($request->user(), "Menghapus file pendukung  <i>{$fileBps->file_name}</i>  BPS"));
        return back()->with('alert-success', 'File pendukung berhasil dihapus');
    }

    public function downloadFile(Request $request, FileBps $fileBps)
    {
        event(new UserLogged($request->user(), "Mendownload file pendukung  <i>{$fileBps->file_name}</i>  BPS"));
        return Storage::download('public/file_pusda/' . $fileBps->file_name);
    }

    public function updateSumberData(Request $request, UraianBps $uraianBps)
    {
        $request->validate(['sumber_data' => ['required', 'exists:skpd,id']]);
        $uraianBps->skpd_id = $request->sumber_data;
        $uraianBps->save();
        event(new UserLogged($request->user(), "Merubah sumber data pada uraian  <i>{$uraianBps->uraian}</i>  BPS"));
        return back()->with('alert-success', 'Sumber data isi uraian berhasil diupdate');
    }

    public function storeTahun(Request $request, TabelBps $tabelBps)
    {
        abort_if(!$request->ajax(), 404);

        $request->validate(['tahun' => ['required', 'array']]);

        $tabelBps->uraianBps->each(function ($uraian) use ($request) {
            foreach ($request->tahun as $tahun) {
                if (!is_null($uraian->parent_id)) {
                    $isiBps = isiBps::where('uraian_bps_id', $uraian->id)->where('tahun', $tahun)->first();
                    if (is_null($isiBps)) {
                        isiBps::create([
                            'uraian_bps_id' => $uraian->id,
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

    public function destroyTahun(Request $request, TabelBps $tabelBps, $year)
    {
        $uraianBps = $tabelBps->uraianBps;
        $uraianBps->each(function ($uraian) use ($year) {
            $uraian->isiBps()->where('tahun', $year)->delete();
        });

        event(new UserLogged($request->user(), 'Menghapus tahun tabel BPS'));

        return back()->with('alert-success', 'Berhasil menghapus tahun');
    }
}

    