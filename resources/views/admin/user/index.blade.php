@extends('templates.app')

@section('content')
    <div class="container mt-3">
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">Tambah Data</a>
        </div>
        <h5 class="mt-3">Data Pengguna (Admin & Staff)</h5>
        <table class="table table-bordered">
            <tr>
                <th></th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            {{-- $cinemas dari compact --}}
            @php
                $no = 1;
            @endphp
            {{-- foreach karena $cinemas pake ::all() datanya lebih dari satu dan berbentuk array --}}
            @foreach ($users as $item)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>
                        @if ($item->role === 'admin')
                            <span class="badge bg-primary-subtle text-primary">Admin</span>
                        @elseif ($item->role === 'staff')
                            <span class="badge bg-success-subtle text-success">Staff</span>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <a href="{{ route('admin.users.edit', $item['id']) }}" class="btn btn-info">Edit</a>
                            <form action="{{ route('admin.users.delete', $item['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
