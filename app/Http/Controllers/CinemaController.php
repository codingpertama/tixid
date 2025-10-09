<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use App\Models\Schedule;

class CinemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cinemas = Cinema::all();
        // $cinema::all() : mengambil semua data pada model cinema (tabel vinemas)
        // mengirim data dari controller ke blade : compact()
        // isi compact sama dengan nama variabel 
        return view('admin.cinema.index', compact('cinemas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cinema.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'nama bioskop harus diisi',
            'location.required' => 'lokasi bioskop harus diisi',
            'location.min' => 'lokasi bioskop harus diisi minimal 10 karakter',
        ]);
        $createData = Cinema::create([
            'name' => $request->name,
            'location' => $request->location,
        ]);
        if ($createData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'berhasil tambah data bioskop!');
        } else {
            return redirect()->back()->with('error', 'gagal silahkan coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cinema $cinema)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // edit($id) -> $id diambil dari route {id}
        $cinema = Cinema::find($id);
        return view('admin.cinema.edit', compact('cinema')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'Nama Bioskop harus diisi',
            'location.required' => 'lokasi Bioskop harus diisi',
            'location.min' => 'lokasi bioskop harus diisi minimal 10 karakter'
        ]);
        // where() : mencari data, format where('nama_column', 'value')
        $updateData = Cinema::where('id', $id)->update([
            'name' => $request->name,
            'location' => $request->location
        ]);
        if ($updateData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'Gagal! silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $schedules = Schedule::where('cinema_id', $id)->count();
        if ($schedules) {
            return redirect()->route('admin.cinemas.index')->with('error', 'tidak dapat menghapus data bioskop data tertaut dengan jadwal tayang');
        }
        // sebelum dihapus, dicari di datanya pake where
        Cinema::where('id', $id)->delete();
        return redirect()->route('admin.cinemas.index')->with('success', 'berhasil hapus data');
    }

    public function exportExcel()
    {
        $fileName = 'data-bioskop.xlsx';
        return Excel::download(new CinemaExport, $fileName);
    }

    public function trash() {
        $cinemaTrash = Cinema::onlyTrashed()->get();
        return view('admin.cinema.trash', compact('cinemaTrash'));
    }

    public function restore($id) {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema->restore();
        return redirect()->route('admin.cinemas.index')->with('success', 'berhasil mengembalikan data');
    }

    public function deletePermanent($id) {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema->forceDelete();
        return redirect()->back()->with('success', 'berhasil menghapus data secara permanen');
    }
}
