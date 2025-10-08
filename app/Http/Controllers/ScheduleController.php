<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Schedule;
use Illuminate\Http\Request;

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
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        //
    }
}
