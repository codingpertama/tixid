<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovieExport;
use App\Models\Promo;
use App\Models\Schedule;
use Yajra\DataTables\Facades\DataTables;

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

    public function datatables() {
        // jika data yang diambil tidak ada relasi gunakan query(), jika ada relasi gunakan with([]) : movie::with(['schedules'])
        // query() : menyiapkan query eloquent model untuk dipake di datatables
        $movies = Movie::query();
        // of() : mengambil query eleqount dari model yang akan diproses datanya
        return DataTables::of($movies)
        // memunculkan angka 1 dst di table
        ->addIndexColumn()
        // addColumn('', function) : membuat column menyajikan data selain data asli dari db
        ->addColumn('imgPoster', function($movie) {
            $imgUrl = asset('storage/' . $movie['poster']);
            return '<img src="' . $imgUrl . '" width="120"/>';
        })
        ->addColumn('activeBadge', function($movie) {
            if ($movie['actived'] == 1) {
                return '<span class="badge badge-success">Aktif</span>';
            } else {
                return '<span class="badge badge-secondary">Non-Aktif</span>';
            }
        })
        ->addColumn('btnActions', function($movie) {
            $btnDetail = '<button class="btn btn-secondary me-2" onclick=\'showModal(' .  json_encode($movie) . ')\'>Detail</button>';

            $btnEdit = '<a href="' . route('admin.movies.edit', $movie['id']) . '" class="btn btn-primary me-2">Edit</a>';

            $btnDelete = '<form action="'. route('admin.movies.delete', $movie['id']) .'" method="POST">' .
                                csrf_field() .
                                method_field('DELETE') .'
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>';

            if ($movie['actived'] == 1) {
                $btnNonAktif = '<form action="'. route('admin.movies.toggle', $movie['id']) .'" method="POST">' .
                                    csrf_field() .
                                    method_field('PATCH') .'
                                    <button type="submit" class="btn btn-danger me-2">Non Aktif</button>
                                </form>';
            } else {
                $btnNonAktif = '';
            }

            return '<div class="d-flex gap-2">' . $btnDetail . $btnEdit . $btnDelete . $btnNonAktif . '</div>';
        })
        ->rawColumns(['imgPoster', 'activeBadge', 'btnActions'])
        ->make(true);
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

    public function homeMovies(Request $request)
    {
        // pengambilan data dari input form search
        //nama inputnya name= search_movie
        $nameMovie = $request->search_movie;
        // jika namemovie (input search diisi tidak kosong)
        if ($nameMovie != "") {
            // like : mencari data yang mirip atau mengandung teks yang diminta 
            // % depan : mencari kata belakang, % belakang : mencari kata depan, % depan belakang mencari dari kata depan dan belakang
            $movies = Movie::where('title', 'LIKE', '%' .$nameMovie. '%')->where('actived', 1)->orderBy('created_at', 'DESC')->get();
        } else {
            $movies = Movie::where('actived', 1)->orderBy('created_at', 'DESC')->get();
        }
        return view('movies', compact('movies'));
    }

    public function movieSchedule($movie_id, Request $request) 
    {
        $sortirHarga = $request->sortirHarga; //mengambil ? bisa dengan request $request
        if ($sortirHarga) {
            // with(['namarelasi] => function($q) {....}) : melakukan filter di relasi
            $movie = Movie::where('id', $movie_id)->with(['schedules' => function($q) use ($sortirHarga) {
                $q->orderBy('price', $sortirHarga);
            }, 'schedules.cinema'])->first();
        } else {
            $movie = Movie::where('id', $movie_id)->with(['schedules', 'schedules.cinema'])->first();
        }

        $sortirAlfabet = $request->sortirAlfabet;
        if ($sortirAlfabet == 'ASC') {
            // karena alfabet dari nama di cinema cinema di schedues.cinema cinema relasi ke dua jadi gunakan collection untuk urutkannya 
            // $movie->schedules : mengambil data dari $movie diatas bagian data schedules nya
            $movie->schedules = $movie->schedules->sortBy(function($schedule) {
                // sortBy() : mengurutkan collection hasil pengambilan data secara ASC
                // diurutkan berdasarkan data di return data nama dari cinema, cinema dari relasi schedule
                return $schedule->cinema->name;
            })->values(); //ambil ulang data hasik sortir : values()
        } elseif ($sortirAlfabet == 'DESC') {
            $movie->schedules = $movie->schedules->sortByDesc(function($schedule) {
                // sortByDesc() : mengurutkan collection hasil pengambilan data secara DESC
                return $schedule->cinema->name;
            })->values();
        }

        return view('schedule.detail-film', compact('movie'));
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
    $schedules = Schedule::where('movie_id', $id)->count();
        if ($schedules) {
            return redirect()->route('admin.movies.index')->with('error', 'tidak dapat menghapus data film data tertaut dengan jadwal tayang');
        }
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

    public function trash() {
        $movieTrash = Movie::onlyTrashed()->get();
        return view('admin.movie.trash', compact('movieTrash'));
    }

    public function restore($id) {
        $movie = Movie::onlyTrashed()->find($id);
        $movie->restore();
        return redirect()->route('admin.movies.index')->with('success', 'berhasil mengembalikan data');
    }

    public function deletePermanent($id) {
        $movie = Movie::onlyTrashed()->find($id);
        $movie->forceDelete();
        return redirect()->back()->with('success', 'berhasil menghapus permanen');
    }
}