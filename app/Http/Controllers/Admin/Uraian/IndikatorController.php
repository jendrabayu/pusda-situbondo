<?php

namespace App\Http\Controllers\Admin\Uraian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Skpd;
use App\Models\TabelIndikator;
use App\Models\UraianIndikator;

class IndikatorController extends Controller
{
    public function index(TabelIndikator $tabelIndikator = null)
    {
        if (empty($tabelIndikator)) {
            $categories = TabelIndikator::all();

            return view('admin.uraian.indikator', compact('categories'));
        } else {
            $categories = TabelIndikator::all();
            $uraian = UraianIndikator::with('childs')
                ->where('tabel_indikator_id', $tabelIndikator->id)
                ->whereNull('parent_id')
                ->orderBy('id')
                ->get();

            return view('admin.uraian.indikator-create', compact('categories', 'uraian', 'tabelIndikator'));
        }
    }

    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'parent_id' => ['nullable', 'numeric'],
            'uraian' => ['required', 'string', 'max:200'],
            'tabel_indikator_id' => ['required', 'numeric', 'exists:tabel_indikator,id'],
        ]);

        UraianIndikator::create($validated);
        return back()->with('alert-success', 'Berhasil menambahkan data');
    }

    public function edit(TabelIndikator $tabelIndikator, UraianIndikator $uraianIndikator)
    {
        $categories = TabelIndikator::all();

        $uraian = UraianIndikator::with('childs')
            ->where('tabel_indikator_id', $tabelIndikator->id)
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        return view('admin.uraian.indikator-edit', compact('categories', 'uraian', 'tabelIndikator', 'uraianIndikator'));
    }

    public function update(Request $request, UraianIndikator $uraianIndikator)
    {
        $validated = $this->validate($request, [
            'parent_id' => ['nullable', 'numeric'],
            'uraian' => ['required', 'string', 'max:200'],

            'tabel_indikator_id' => ['required', 'numeric', 'exists:tabel_indikator,id'],
        ]);

        $uraianIndikator->update($validated);
        return back()->with('alert-success', 'Data berhasil diupdate');
    }

    public function destroy(UraianIndikator $uraianIndikator)
    {
        $uraianIndikator->delete();
        return back()->with('alert-success', 'Data berhasil dihapus');
    }
}