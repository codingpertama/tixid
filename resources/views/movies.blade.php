@extends('templates.app')
@section('content')
    <div class="container my-5">
        <h5 class="mb-5">Seluruh Film sedang tayang</h5>
        <div class="d-flex justify-content-center gap-2 my-3">
            @foreach ($movies as $movie)
            <div class="card" style="width: 13rem;">
                <img src="{{ asset('storage/' . $movie->poster) }}"
                class="card-img-top" alt="{{ $movie->title }}" style="height: 300px; object-fit: cover;">
                <div class="card-body" style="padding: 0 !important">
                    <p class="card-text text-center bg-primary py-2"><a href="{{ route('detail', $movie->id) }}"
                        class="text-warning"><b>beli tiket</b></a></p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection