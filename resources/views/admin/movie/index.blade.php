    @extends('templates.app')
    @section('content')
        <div class="container my-5">
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.movies.export') }}" class="btn btn-secondary me-2">Export</a>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-success">Tambah Data</a>
            </div>

            @if (Session::get('success'))
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                </div>
            @endif

            <h5 class="mb-3">Data Film</h5>
            <table class="table table-bordered">
                <tr>
                    <th>#</th>
                    <th>Poster</th>
                    <th>Judul Film</th>
                    <th>Status Aktif</th>
                    <th>Aksi</th>
                </tr>
                @foreach ($movies as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <img src="{{ asset('storage/' . $item['poster']) }}" width="120">
                        </td>
                        <td>{{ $item['title'] }}</td>
                        <td>
                            @if ($item['actived'] == 1)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-secondary me-2" onclick="showModal({{ $item }})">Detail</button>
                            <a href="{{ route('admin.movies.edit', $item['id']) }}" class="btn btn-primary me-2">Edit</a>

                            <form action="{{ route('admin.movies.delete', $item['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>

                            {{-- Tombol toggle --}}
                            <form action="{{ route('admin.movies.toggle', $item['id']) }}" method="POST" class="ms-2">
                                @csrf
                                @method('PATCH')
                                @if ($item['actived'] == 1)
                                    <button type="submit" class="btn btn-warning">Non-Aktif Film</button>
                                @endif
                            </form>
                        </td>

                    </tr>
                @endforeach
            </table>

            <!-- Modal -->
            <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modalDetailLabel">Detail Film</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="modalDetailBody">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    {{-- Script --}}
    @push('script')
        <script>
            function showModal(item) {
                // menyiapkan gambar
                let image = "{{ asset('storage') }}" + "/" + item.poster;

                let content = `
                    <img src="${image}" width="120" class="d-block mx-auto my-3">
                    <ul>
                        <li>Judul: ${item.title}</li>
                        <li>Durasi: ${item.duration}</li>
                        <li>Genre: ${item.genre}</li>
                        <li>Sutradara: ${item.director}</li>
                        <li>Usia Minimal: <span class="badge badge-danger">${item.age_rating}</span></li>
                        <li>Sinopsis: ${item.description}</li>
                    </ul>
                `;

                // taruh ke modal
                let modalDetailBody = document.querySelector('#modalDetailBody');
                modalDetailBody.innerHTML = content;

                let modalDetail = document.querySelector("#modalDetail");
                // munculkan modal bootstrap modal yg id nya modaldetail
                new bootstrap.Modal(modalDetail).show();
            }
        </script>
    @endpush
