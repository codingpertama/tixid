@extends('templates.app')

@section('content')
    <div class="container mt-3">
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.users.index')}}" class="btn btn-secondary me-2">Kembali</a>
        </div>
        <h5 class="mt-3">Data Pengguna (Admin & Staff)</h5>
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
            {{-- $cinemas dari compact --}}
            @php
                $no = 1;
            @endphp
            {{-- foreach karena $cinemas pake ::all() datanya lebih dari satu dan berbentuk array --}}
            @foreach ($userTrash as $item)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="d-flex gap-2">
                            <form action="{{ route('admin.users.restore', $item->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary">Kembalikan</button>
                            </form>
                            <form action="{{ route('admin.users.delete_permanent', $item->id) }}" method="POST">
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
