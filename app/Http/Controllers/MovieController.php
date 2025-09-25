<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovieExport;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movies = Movie::all();
        return view('admin.movie.index', compact('movies'));
    }

    public function home()
    {
        // where(): untuk mencari data format yang digunakan where('kolom', 'operator', 'nilai')
        // get() : mengambil semua data hasil filter 
        // first() : mengambil 1 data pertama hasil filter
        // paginate(): membagi data menjadi beberapa halaman
        // orderBy : untuk mengurutkan data format orderBy('field', 'type')
        // type ASC : mengurutkan dari a-z atau 0-9 atau terlama ke terbaru 
        // type DESC : mengurutkan dari z-a atau 9-0 atau terbaru ke terlama
        // limit() : membatasi jumlah data yang diambil
        $movies = Movie::where('actived', 1)->orderBy('created_at', 'desc')->limit(4)->get();
        return view('home', compact('movies'));
    }

    public function homeMovies()
    {
        $movies = Movie::where('actived', 1)->orderBy('created_at', 'desc')->get();
        return view('movies', compact('movies'));
    }

    public function detail($id)
    {
        $movie = Movie::find($id);
        return view('schedule.detail-film', compact('movie'));
    }

    public function toggle($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->actived = $movie->actived ? 0 : 1; // toggle status
        $movie->save();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Berhasil non-aktif data film!');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.movie.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required',
            // mimes: memastikan extensi file (bentuk file yang boleh di upload)
            'poster' => 'required|mimes:jpg,jpeg,png,svg,webp',
            'description' => 'required| min:10'
        ], [
            'title.required' => 'judul fim harus diisi',
            'duration.required' => 'durasi film harus diisi',
            'genre.required' => 'genre film harus diisi',
            'director.required' => 'sutradara harus diisi',
            'age_rating.required' => 'usia minimal harus diisi',
            'poster.required' => 'poster harus diisi',
            'poster.mimes' => 'poster harus berbentuk jpg/jpeg/png/svg/webp',
            'description.required' => 'sinopsis harus diisi',
            'description.min' => 'sinopsis diisi minimal 10 karakter'
        ]);
        // ambil file dari input
        $poster = $request->file('poster');
        // buat nama file yang akan disimpan di folder public/storage
        // nama dibuat baru dan unik untuk menghindari duplikasi file : <acak>-poster.jpg contoh nama barunya
        // getClientOriginalExtension() : mengambil ekstensi file yang diupload
        $namaFile = rand(1, 10) . "-poster." . $poster->getClientOriginalExtension();
        // simpan file ke folder public/storage
        // storeAs("namaFolder", namaFile, "visibility")
        // visibility : public/private (disesuaikan file boleh nampilin atau nggak)
        $path = $poster->storeAs("poster", $namaFile, "public");

        $createData = Movie::create([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            // yang disimpan di DB bukan filenya, hanya lokasi file dari storeAs() -> $path
            'poster' => $path,
            'description' => $request->description,
            'actived' => 1
        ]);
        if ($createData) {
            return redirect()->route('admin.movies.index')->with('success', 'berhasil membuat data');
        } else {
            return redirect()->back()->with('error', 'gagal menambahkan data!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movie = Movie::find($id);
        return view('admin.movie.edit', compact('movie'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required',
            // mimes: memastikan extensi file (bentuk file yang boleh di upload)
            'poster' => 'mimes:jpg,jpeg,png,svg,webp',
            'description' => 'required| min:10'
        ], [
            'title.required' => 'judul fim harus diisi',
            'duration.required' => 'durasi film harus diisi',
            'genre.required' => 'genre film harus diisi',
            'director.required' => 'sutradara harus diisi',
            'age_rating.required' => 'usia minimal harus diisi',
            'poster.required' => 'poster harus diisi',
            'poster.mimes' => 'poster harus berbentuk jpg/jpeg/png/svg/webp',
            'description.required' => 'sinopsis harus diisi',
            'description.min' => 'sinopsis diisi minimal 10 karakter'
        ]);
        // ambil data sebelumnya
        $movie = Movie::find($id);
        // cek jika ada poster baru
        if ($request->file('poster')) {
            // ambil lokasi poster lama : storage_path()
            $posterSebelumnya = storage_path('app/public/' . $movie['poster']);
            // cek jika file ada di folder storage : file_exists()
            if (file_exists($posterSebelumnya)) {
                // hapus file sebelumnya : unlink()
                unlink($posterSebelumnya);
            }

            // ambil file dari input
            $poster = $request->file('poster');
            // buat nama file yang akan disimpan di folder public/storage
            // nama dibuat baru dan unik untuk menghindari duplikasi file : <acak>-poster.jpg contoh nama barunya
            // getClientOriginalExtension() : mengambil ekstensi file yang diupload
            $namaFile = rand(1, 10) . "-poster." . $poster->getClientOriginalExtension();
            // simpan file ke folder public/storage
            // storeAs("namaFolder", namaFile, "visibility")
            // visibility : public/private (disesuaikan file boleh nampilin atau nggak)
            $path = $poster->storeAs("poster", $namaFile, "public");
        }
        

        $createData = Movie::where('id', $id)->update([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            // ?? ternary : (if, jika ada ambil) ?? (else, jika tidak ada gunakan yang disini)
            'poster' => $path ?? $movie['poster'],
            'description' => $request->description,
            'actived' => 1
        ]);
        if ($createData) {
            return redirect()->route('admin.movies.index')->with('success', 'berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'gagal mengubah data!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    // findorFail() : mencari data berdasarkan id, jika tidak ada akan menampilkan error 404
    // storage::disk('public')->exists() : mengecek apakah file ada di folder storage
    // storage::disk('public')->delete() : menghapus file di folder storage 
    $movie = Movie::findOrFail($id);

    // hapus file poster dari storage kalau ada
    if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
        Storage::disk('public')->delete($movie->poster);
    }

    // hapus data film
    $movie->delete();

    return redirect()->route('admin.movies.index')->with('success', 'Berhasil menghapus data film!');
}

    public function exportExcel()
    {
        // nama file yang akan terunduh=
        $fileName = 'data-film.xlsx';
        // proses download
        return Excel::download(new MovieExport, $fileName);
    }
}