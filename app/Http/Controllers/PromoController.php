<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use App\Exports\PromoExport;
use App\Models\Schedule;
use Yajra\DataTables\Facades\DataTables;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promos = Promo::all();
        return view('staff.promo.index', compact('promos'));
    }

    public function datatables() {
        $promos = Promo::query()->get();
        return DataTables::of($promos)
        ->addIndexColumn()
        ->addColumn('discount', function($promo) {
            if ($promo->type == 'percent') {
                return $promo->discount . '%';
            } else {
                return 'Rp ' . number_format($promo->discount, 0, ',', '.');
            }
        })
        ->addColumn('btnActions', function($promo) {
            $btnEdit = '<a href="' . route('staff.promos.edit', $promo['id']) . '" class="btn btn-primary me-2">Edit</a>';
            $btnDelete = '<form action="'. route('staff.promos.delete', $promo['id']) .'" method="POST">' .
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
        return view('staff.promo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'promo_code' => 'required',
            'discount' => 'required',
            'type' => 'required',
        ], [
            'promo_code.required' => 'kode promo harus diisi',
            'discount.required' => 'diskon harus diisi',
            'type.required' => 'tipe harus diisi',
        ]);
        if ($request->type == 'percent' && $request->discount > 100) {
            return back()->withErrors([
                'discount' => 'Diskon persen tidak boleh lebih dari 100'
            ])->withInput();
        } elseif ($request->type == 'rupiah' && $request->discount < 1000) {
            return back()->withErrors([
                'discount' => 'Diskon rupiah tidak boleh kurang dari 1000'
            ])->withInput();
        }
        $createData = Promo::create([
            'promo_code' => $request->promo_code,
            'discount' => $request->discount,
            'type' => $request->type,
            'actived' => 1
        ]);
        if ($createData) {
        return redirect()->route('staff.promos.index')->with('success', 'berhasil tambah data promo!');
        } else {
            return redirect()->back()->with('error', 'gagal silahkan coba lagi');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Promo $promo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $promo = Promo::find($id);
        return view('staff.promo.edit', compact('promo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'promo_code' => 'required',
            'discount' => 'required',
            'type' => 'required',
        ], [
            'promo_code.required' => 'kode promo harus diisi',
            'discount.required' => 'diskon harus diisi',
            'type.required' => 'tipe harus diisi',
        ]);

        if ($request->type == 'percent' && $request->discount > 100) {
            return back()->withErrors([
                'discount' => 'Diskon persen tidak boleh lebih dari 100'
            ])->withInput();
        } elseif ($request->type == 'rupiah' && $request->discount < 1000) {
            return back()->withErrors([
                'discount' => 'Diskon rupiah tidak boleh kurang dari 1000'
            ])->withInput();
        }

        $updateData = Promo::where('id', $id)->update([
            'promo_code' => $request->promo_code,
            'discount' => $request->discount,
            'type' => $request->type,
        ]);
        if ($updateData) {
            return redirect()->route('staff.promos.index')->with('success', 'berhasil update data promo!');
        } else {
            return redirect()->back()->with('error', 'gagal silahkan coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Promo::where('id', $id)->delete();
        return redirect()->route('staff.promos.index')->with('success', 'berhasil hapus data promo!');
    }

    public function exportExcel()
    {
        $fileName = 'data-promo.xlsx';
        return Excel::download(new PromoExport, $fileName);
    }

    public function trash() {
        $promoTrash = Promo::onlyTrashed()->get(); //karna gak ada tabel relasi jadi gak pake with
        return view('staff.promo.trash', compact('promoTrash'));
    }

    public function restore($id) {
        $promo = Promo::onlyTrashed()->find($id);
        $promo->restore();
        return redirect()->route('staff.promos.index')->with('success', 'berhasil mengembalikan data');
    } 

    public function deletePermanent($id) {
        $promo = Promo::onlyTrashed()->find($id);
        $promo->forceDelete();
        return redirect()->back()->with('success', 'berhasil menghapus permanen');
    }
}
