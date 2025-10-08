@extends('templates.app')
@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdd">Tambah Data</button>
        </div>
        <h3 class="my-3">Data Jadwal Tayangan</h3>
        @if (Session::get('success'))
            <div class="alert alert-success">{{Session::get('success')}}</div>
        @endif
        <table class="table table-bordered">
            <tr>
                <th>#</th>
                <th>Nama Bioskop</th>
                <th>Judul Film</th>
                <th>Jam Tayang</th>
                <th>Aksi</th>
            </tr>
            @foreach ($schedules as $key => $schedule)
                <tr>
                    <td>{{$key + 1}}</td>
                    {{-- mengambil relasi $item['namarelasi']['data'] --}}
                    <td>{{ $schedule['cinema']['name'] }}</td>
                    <td>{{ $schedule['movie']['title'] }}</td>
                    <td>
                        <ul>
                            {{-- karna hours array gunakan loop --}}
                            @foreach ($schedule['hours'] as $hours)
                                <li>{{$hours}}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="d-flex">
                        <a href="" class="btn btn-primary">Edit</a>
                        <button class="btn btn-danger ms-2">Hapus</button>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAddLabel">Tambah Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('staff.schedules.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Bioskop</label>
                            <select name="cinema_id" id="cinema_id"
                                class="form-select @error('cinema_id') is-invalid @enderror">
                                <option disabled hidden selected>Pilih Bioskop</option>
                                {{-- munculin data cinemas --}}
                                @foreach ($cinemas as $cinema)
                                    {{-- opsi select muncul sesuai data --}}
                                    {{-- yang disimpan di db id cinema yang dimunculin name --}}
                                    <option value="{{ $cinema['id'] }}">{{$cinema['name']}}</option>
                                @endforeach
                            </select>
                            @error('cinema_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Film</label>
                            <select name="movie_id" id="movie_id"
                                class="form-select @error('movie_id') is-invalid @enderror">
                                <option disabled hidden selected>Pilih Film</option>
                                @foreach ($movies as $movie)
                                    <option value="{{ $movie['id'] }}">{{ $movie['title'] }}</option>
                                @endforeach
                            </select>
                            @error('movie_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Harga</label>
                            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror">
                            @error('price')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            {{-- jika ada err validasi array hours --}}
                            @if ($errors->has('hours.*'))
                                <small class="text-danger">{{$errors->first('hours.*')}}</small>
                            @endif
                            <label for="hours" class="form-label">Jam Tayang</label>
                            <input type="time" name="hours[]" id="hours" class="form-control @if ($errors->has('hours.*')) is-invalid @endif">
                            {{-- wadah untuk penambahan input dari js --}}
                            <div id="additionalInput"></div>
                            <span class="text-primary mt-3" style="cursor: pointer" onclick="addInput()">+Tambah Input</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function addInput() {
            let content = `<input type="time" name="hours[]" class="form-control mt-3">`;
            // panggil bagian yang akan diisi
            let wadah = document.querySelector("#additionalInput");
            // tambah konten karna akan terus bertambah gunakan +=
            wadah.innerHTML += content;
        }
    </script>
    {{-- cek apakah ada error di php atau ngga --}}
    @if ($errors->any())
        {{-- jika ada error, munculkan modal melalui javascript --}}
        <script>
            let modalAdd = document.querySelector('#modalAdd');
            new bootstrap.Modal(modalAdd).show();
        </script>
    @endif
@endpush
