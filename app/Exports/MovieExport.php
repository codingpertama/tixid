<?php

namespace App\Exports;

use App\Models\Movie;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class MovieExport implements FromCollection, WithHeadings, WithMapping
{
    private $key = 0;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // memanggil data yang akan di munculind di excel
        return Movie::all();
    }

    // menentukan header data (th)
    public function headings(): array
    {
        return ['No', 'Judul Film', 'Durasi', 'Genre', 'Sutradara', 'Usia Minimal', 'Poster', 'Sinopsis'];
    }

    // menentukan isi data (td)
    public function map($movie): array
    {
        return [
            // menambah sebanyak 1 setiap data dari $key = 0
            ++$this->key,
            $movie->title,
            // format jadi 01 jam 30 menit
            // parse() : untuk mengamil data yang akan dimanipulasi
            // format() : untuk format tanggal/jam
            Carbon::parse($movie->duration)->format('H') . ' Jam ' . 
            Carbon::parse($movie->duration)->format('i') . ' Menit',
            $movie->genre,
            $movie->director,
            // format usia 17+
            $movie->age_rating . '+',
            // asset() : link buat liat gambar
            asset('storage') . '/' . $movie->poster,
            $movie->description,
        ];
    }
}
