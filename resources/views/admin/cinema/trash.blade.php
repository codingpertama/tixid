@extends('templates.app')

@section('content')
    <div class="container mt-3">
        @if (Session::get('success'))
            <div class="alert alert-success">{{Session::get('success')}}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-secondary me-2">Kembali</a>
        </div>
        <h5 class="mt-3">Data sampah Bioskop</h5>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Nama Bioskop</th>
                <th>Lokasi</th>
                <th>Aksi</th>
            </tr>
            {{-- $cinemas dari compact --}}
            {{-- foreach karena $cinemas pake ::all() datanya lebih dari satu dan berbentuk array --}}
            @foreach ($cinemaTrash as $key => $item)
                <tr>
                    {{-- $key: index array dari 0 --}}
                    <td>{{$key+1}}</td>
                    {{-- name dan location dari fillable --}}
                    <td>{{$item['name']}}</td>
                    <td>{{$item['location']}}</td>
                    <td class="d-flex"> 
                        <form action="{{ route('admin.cinemas.restore', $item->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-secondary">Kembalikan</button>
                        </form>
                        <form action="{{ route('admin.cinemas.delete_permanent', $item->id) }}" method="POST">
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