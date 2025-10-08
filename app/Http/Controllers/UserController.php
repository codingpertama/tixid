<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use App\Exports\UserExport;

class UserController extends Controller
{

    public function register(Request $request)
    {
        // request $request : mengambil value request/input
        //dd() : debugging, cek data sebelum diproses
        // dd($request->all());

        // validasi 
        $request->validate([
            // format : 'name_input' => validasi
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            // email : dns memastikan email valid
            'email' => 'required|email:dns',
            'password' => 'required'
        ], [
            // kustom pesan
            // format : 'name_input.validasi' => 'pesan error'
            'first_name.required' => 'nama depan wajib diisi',
            'first_name.min' => 'nama depan diisi menimal 3 karakter',
            'last_name.required' => 'nama belakang wajib diisi',
            'last_name.min' => 'nama belakang diisi minimal 3 karakter',
            'email.required' => 'email wajib diisi',
            'email.email' => 'email diisi dengan data valid',
            'password.required' => 'password wajib diisi'
        ]);

        // elequent(fungsi model) tambah data baru : create([])
        $createData = User::create([
            // 'column' => request->name_input
            'name' => $request->first_name . " " . $request->last_name,
            'email' => $request->email,
            // enkripsi data : merubah menjadi karakter acak, tidak ada yng bisa tau isi datanya : Hash::make()
            'password' => Hash::make($request->password),
            // role diisi langsung sebagai user agar tidak bisa menjadi admin/staff bagi pendaftar akun
            'role' => 'user'
        ]);

        if($createData) {
            // redirect() perpindahan halaman, route() name route yang akan dipanggil
            return redirect()->route('login')->with('success', 'berhasil membuat akun, silahkan login!');
        } else {
            // back() kembali ke halaman sebelumnya yang dia akses
            return redirect()->back()->with('error', 'gagal silahkan coba lagi.');
        }
    }

    public function loginAuth(Request $request) {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ], [
            'email.required' => 'email harus diisi',
            'password.required' => 'password harus diisi'
        ]);
        // menyimpan data yagn akan diverifikasi
        $data = $request->only(['email', 'password']);
        // auth::attempt() -> verifikasi kecocokan email-pw atau username-pw
        if (Auth::attempt($data)) {
            if (Auth::user()->role == 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'berhasil login!');
            } elseif (Auth::user()->role == 'staff') {
                return redirect()->route('staff.dashboard')->with('success', 'berhasil login!');
            } else {
                return redirect()->route('home')->with('success', 'berhasil login!');
            }
        } else {
            return redirect()->back()->with('error', 'gagal pastikan email dan password sesuai');
        }
    }

    public function logout() {
        // Auth::logout() : hapus sesi login
        Auth::logout();
        return redirect()->route('home')->with('logout', 'anda sudah logout silahkan login kembali untuk akses lengkap');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->filter(function ($user) {
    return in_array($user->role, ['admin', 'staff']);
});
        return view('admin.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|min:3',
        'email' => 'required',
        'password' => 'required'
    ]);

    $createData = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'staff'
    ]);

    if ($createData) {
        return redirect()->route('admin.users.index')->with('success', 'Staff berhasil ditambahkan!');
    } else {
        return redirect()->back()->with('error', 'Gagal menambahkan staff, coba lagi.');
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // edit($id) -> $id diambil dari route {id}
        $user = User::find($id);
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required',
        ], [
            'name.required' => 'nama harus diisi',
            'name.min' => 'nama diisi minimal 3 karakter',
            'email.required' => 'email harus diisi',
        ]);
        // where() : mencari data, format where('nama_column', 'value')
        $updateData = User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        if ($updateData) {
            return redirect()->route('admin.users.index')->with('success', 'berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'Gagal! silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

    if ($user) {
        $user->delete(); // soft delete
        return redirect()->route('admin.users.index')->with('success', 'Berhasil menghapus data (soft delete)');
    } else {
        return redirect()->route('admin.users.index')->with('error', 'User tidak ditemukan');
    }
    }

    public function exportExcel()
    {
        $fileName = 'data-pengguna.xlsx';
        return Excel::download(new UserExport, $fileName);
    }
}
