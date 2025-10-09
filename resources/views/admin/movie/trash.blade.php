@extends('templates.app')
@section('content')
@if (Session('success'))
    <div class="alert alert-success">
        {{ Session('success') }}
    </div>
@endif
    <div class="container my-5">
        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary me-2">Kembali</a>
        </div>
        <h5 class="mb-3">Data Sampah Promo</h5>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Judul Film</th>
                <th>Aksi</th>
            </tr>
            @foreach ($movieTrash as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item['title'] }}</td>
                    <td class="d-flex">
                        <form action="{{ route('admin.movies.restore', $item->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-secondary">Kembalikan</button>
                        </form>
                        <form action="{{ route('admin.movies.delete_permanent', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                        </form>
                    </td>

                </tr>
            @endforeach
        </table>
    @endsection
