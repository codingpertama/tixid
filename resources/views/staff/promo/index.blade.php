@extends('templates.app')

@section('content')
    <div class="container mt-3">
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        <div class="d-flex justify-content-end">
            <a href="{{ route('staff.promos.export') }}" class="btn btn-secondary me-2">Export</a>
            <a href="{{ route('staff.promos.create') }}" class="btn btn-success">Tambah Data</a>
        </div>

        <h5 class="mt-3">Data Promo</h5>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Kode Promo</th>
                <th>Total Potongan</th>
                <th>Aksi</th>
            </tr>

            @foreach ($promos as $key => $item)
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
                        <a href="{{ route('staff.promos.edit', $item['id']) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('staff.promos.delete', $item['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection