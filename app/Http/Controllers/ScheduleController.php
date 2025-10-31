<?php

namespace App\Http\Controllers;

use App\Exports\ScheduleExport;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cinemas = Cinema::all();
        $movies = Movie::all();

        // karena cinema_id dan movie_id di db hanya berupa angka, untuk mengambil detail realasi gunakan eloquent with()
        // with() : mengambil detail data relasi, diambil dari nama fungsi relasi di model
        $schedules = Schedule::with(['cinema', 'movie'])->get();

        return view('staff.schedule.index', compact('cinemas', 'movies', 'schedules'));
    }

    public function datatables() {
        $schedules = Schedule::with(['cinema', 'movie'])->get();
        return DataTables::of($schedules)
        ->addIndexColumn()
        ->addColumn('cinema_name', function($schedule) {
            return $schedule->cinema->name;
        })
        ->addColumn('movie_title', function($schedule) {
            return $schedule->movie->title;
        })
        ->addColumn('price', function($schedule) {
            return 'Rp. ' . number_format($schedule->price, 0, ',', '.');
        })
        ->addColumn('hours', function($schedule) {
            $list = '';
            foreach($schedule->hours as $hour) {
                $list .= '<li>' . $hour . '</li>';
            }
            return '<ul>' . $list . '</ul>';
        })
        ->addColumn('btnActions', function($schedule) {
            $btnEdit = '<a href="' . route('staff.schedules.edit', $schedule['id']) . '" class="btn btn-primary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('staff.schedules.delete', $schedule['id']) .'" method="POST">' .
                            csrf_field() .
                            method_field('DELETE') .'
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>';

            return '<div class="d-flex gap-2">' . $btnEdit . $btnDelete . '</div>';
        })
        ->rawColumns(['price', 'hours', 'btnActions'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cinema_id' => 'required',
            'movie_id' => 'required',
            'price' => 'required|numeric',
            // karna hours array, yang divalidasi isi array nya (tanda .) dan di validasi semua isi item array (tanda *)
            'hours.*' => 'required|date_format:H:i'
        ], [
            'cinema_id.required' => 'bioskop harus dipilih',
            'movie_id.required' => 'film harus dipilih',
            'price.required' => 'harga harus diisi',
            'price.numeric' => 'harga harus diisi dengan angka',
            'hours.*.required' => 'jam tayang harus diisi minimal 1 data',
            'hours.*.date_format' => 'jam tayang harus diisi dengan jam:menit'
        ]);

        // pengecekan apakah ada bioskop dan file yang dipilih sekarang di db nya kaalau ada ambil jamnya
        $hours = Schedule::where('cinema_id', $request->cinema_id)->where('movie_id', $request->movie_id)->value('hours');
        // jika sudah ada data dengan bioskop dan film yang sama maka ambil data jam tsb. jika tidak ada buat array kosong
        $hoursBefore = $hours ?? [];
        // gabungkan array jam sebelumnya dengan array jam yang baru diambahin
        $mergeHours = array_merge($hoursBefore, $request->hours);
        // jika ada jam yang duplikat ambil salah satu
        // gunakan data ini untuk disimpan di db
        $newHours = array_unique($mergeHours);

        // updateOrCreate : mengubah jika sudah ada menambahkan jika belum ada
        $createData = Schedule::updateOrCreate([
            // array pertama, acuan pencarian data 
            'cinema_id' => $request->cinema_id,
            'movie_id' => $request->movie_id,
        ], [
            // array kedua , data yang akan diupdate
            'price' => $request->price,
            'hours' => $newHours,
        ]);
        if ($createData) {
            return redirect()->route('staff.schedules.index')->with('success', 'berhasil menambahkan data');
        } else {
            return redirect()->route('staff.schedules.index')->with('error', 'gagal coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule, $id)
    {
        $schedule = Schedule::where('id', $id)->with(['cinema', 'movie'])->first();
        return view('staff.schedule.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'hours.*' => 'required|date_format:H:i'
        ], [
            'price.required' => 'harga harus diisi',
            'price.numeric' => 'harga harus diisi dengan angka',
            'hours.*.required' => 'jam tayang harus diisi',
            'hours.*.date_format' => 'jam tayang harus diisi dengan format jam:menit',
        ]);

        $updateData = Schedule::where('id', $id)->update([
            'price' => $request->price,
            'hours' => $request->hours
        ]);

        if($updateData) {
            return redirect()->route('staff.schedules.index')->with('success', 'berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'gagal coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule, $id)
    {
        Schedule::where('id', $id)->delete();
        return redirect()->route('staff.schedules.index')->with('success', 'berhasil menghapus data');
    }

    public function trash() {
        $scheduleTrash = Schedule::with(['cinema', 'movie'])->onlyTrashed()->get();
        return view('staff.schedule.trash', compact('scheduleTrash'));
    }

    public function restore($id) {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->restore();
        return redirect()->route('staff.schedules.index')->with('success', 'berhasil mengembalikan data');
    }

    public function deletePermanent($id) {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->forceDelete();
        return redirect()->back()->with('success', 'berhasil menghapus permanen');
    }

    public function exportExcel() {
        $fileName = 'data-schedule.xlsx';
        return Excel::download(new ScheduleExport, $fileName);
    }
}
