<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use App\Models\Schedule;
use Yajra\DataTables\Facades\DataTables;

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

    public function datatables() {
        $cinemas = Cinema::query();
        return DataTables::of($cinemas)
        ->addIndexColumn()
        ->addColumn('btnActions', function($cinema) {
            $btnEdit = '<a href="' . route('admin.cinemas.edit', $cinema['id']) . '" class="btn btn-primary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('admin.cinemas.delete', $cinema['id']) .'" method="POST">' .
                                csrf_field() .
                                method_field('DELETE') .'
                                <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>';

        return '<div class="d-flex gap-2">' . $btnEdit . $btnDelete . '</div>';
        })
        ->rawColumns(['btnActions'])
        ->make(true);
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

    public function cinemaList() {
        $cinemas = Cinema::all();
        return view('schedule.cinemas', compact('cinemas'));
    }

    public function cinemaSchedules($cinema_id) {
        // whereHas('namarelasi', function($q) {....}) : argumen 1 nama relasi majib argumen 2 function untuk filter pada relasi optional
        // whereHas('namarelasi') : Movie:whereHas('schedules') mengambil data film hanya yang memiliki relasi (memiliki data) schedules
        // whereHas('namarelasi', function($q) {....}) -> schedule::whereHas('movie', function($q) { $q->where('actived', 1); }) : mengambil data schedules hanya yang memiliki relasi (memiliku data ) movie dan nilai actived pada movienya 1
        $schedules = Schedule::where('cinema_id', $cinema_id)->with('movie')->whereHas('movie', function($q) {
            $q->where('actived', 1);
        })->get();
        
        return view('schedule.cinema-schedules', compact('schedules'));
    }
}
