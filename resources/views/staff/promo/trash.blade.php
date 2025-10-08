@extends('templates.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    
@endif
    <div class="container mt-3">
        <div class="d-flex justify-content-end">
            <a href="{{ route('staff.promos.index') }}" class="btn btn-secondary me-2">Kembali</a>
        </div>

        <h5 class="mt-3">Data Sampah Promo</h5>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Kode Promo</th>
                <th>Total Potongan</th>
                <th>Aksi</th>
            </tr>

            @foreach ($promoTrash as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->promo_code }}</td>
                    <td>
                        @if ($item->type == 'percent')
                            {{ $item->discount }}%
                        @else
                            Rp {{ number_format($item->discount, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="d-flex gap-1"> 
                        <form action="{{ route('staff.promos.restore', $item->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-secondary">Kembalikan</button>
                        </form>
                        <form action="{{ route('staff.promos.delete_permanent', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection