<?php

namespace App\Http\Controllers\Admin\TreeView;

use App\Http\Controllers\Controller;
use App\Models\TabelBps;
use Illuminate\Http\Request;

class BpsController extends Controller
{
    public function index()
    {
        $categories = TabelBps::all();

        return view('admin.treeview.bps', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'parent_id' =>  ['required', 'numeric', 'exists:tabel_bps,id'],
            'menu_name' => ['required', 'string', 'max:100']
        ]);

        TabelBps::create($validated);

        return back()->with('alert-success', 'Berhasil menambahkan data');
    }

    public function edit($id)
    {
        $tabelBps = TabelBps::findOrFail($id);
        $categories = TabelBps::all();

        return view('admin.treeview.bps-edit', compact('categories', 'tabelBps'));
    }

    public function update(Request $request, $id)
    {
        $tabelBps = TabelBps::findOrFail($id);
        $validated = $this->validate($request, [
            'parent_id' =>  ['required', 'numeric', 'exists:tabel_bps,id'],
            'menu_name' => ['required', 'string',  'max:100']
        ]);

        $tabelBps->update($validated);

        return back()->with('alert-success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $tabelBps = TabelBps::findOrFail($id);
        $tabelBps->delete();
        $tabelBps->childs()->delete();

        return back()->with('alert-success', 'Data berhasil dihapus');
    }
}