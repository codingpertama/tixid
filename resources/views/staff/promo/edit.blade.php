@extends('templates.app')

@section('content')
    <div class="w-75 d-block mx-auto my-5 p-4">
        <h5 class="text-center my-3">Edit Data Promo</h5>
        <form method="POST" action="{{ route('staff.promos.update', $promo['id']) }}">
            @csrf
            @method('PUT')
            {{-- Kode Promo --}}
            <div class="mb-3">
                <label for="promo_code" class="form-label">Kode Promo :</label>
                <input type="text" 
                       class="form-control @error('promo_code') is-invalid @enderror" 
                       id="promo_code" 
                       name="promo_code" 
                       value="{{ $promo['promo_code'] }}">
                @error('promo_code')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tipe Promo --}}
            <div class="mb-3">
                <label for="type" class="form-label">Tipe Promo :</label>
                <select name="type" id="type" 
                        class="form-select @error('type') is-invalid @enderror">
                    <option selected hidden>Pilih</option>
                    <option value="percent" {{ $promo['type'] == 'percent' ? 'selected' : '' }}>%</option>
                    <option value="rupiah" {{ $promo['type'] == 'rupiah' ? 'selected' : '' }}>Rupiah</option>
                </select>
                @error('type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Discount / Jumlah Potongan --}}
            <div class="mb-3">
                <label for="discount" class="form-label">Jumlah Potongan :</label>
                <input type="number" 
                       class="form-control @error('discount') is-invalid @enderror" 
                       id="discount" 
                       name="discount" 
                       value="{{ $promo['discount'] }}">
                @error('discount')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button class="btn btn-primary" type="submit">Edit Data</button>
        </form>
    </div>
@endsection
