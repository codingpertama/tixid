<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\Schedule;
use App\Models\Ticket;
use App\Models\TicketPayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class TicketController extends Controller
{
    public function showSeats($scheduleId, $hourId) {
        $schedule = Schedule::find($scheduleId);
        // ambil jam yang index nya sesuai params route
        $hour = $schedule['hours'][$hourId] ?? ''; //kalau tidak ketemu jam nya buat default kosong

        $soldSeats = Ticket::where('schedule_id', $scheduleId)->where('actived', 1)->where('date', now()->format('Y-m-d'))->pluck('rows_of_seats');
        // pluck : ambil datanya hanya dari 1 column

        $soldSeatsFormat = [];
        foreach ($soldSeats as $seat) {
            foreach ($seat as $item) {
                array_push($soldSeatsFormat, $item);
            }
        }
        // dd($soldSeatsFormat);
        return view('schedule.row-seats', compact('schedule', 'hour', 'soldSeatsFormat'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $ticketActive = Ticket::where('user_id', $userId)->where('actived', 1)->where('date', now()->format('Y-m-d'))->get();
        // ambil data tiket berdasarkan daya siapa yang login yang non aktif dan sudah kadaluarsa
        $ticketNonActive = Ticket::where('user_id', $userId)->where('date', '<>', now()->format('Y-m-d'))->get();
        // <> artinya tidak sama dengan
        return view('ticket.index', compact('ticketActive', 'ticketNonActive'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'schedule_id' => 'required',
            'date' => 'required',
            'hour' => 'required',
            'rows_of_seats' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'service_fee' => 'required',
        ]);

        $createData = Ticket::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'hour' => $request->hour,
            'rows_of_seats' => $request->rows_of_seats,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
            'service_fee' => $request->service_fee,
            'actived' => 0, //belum aktif sebelum dibayar
        ]);

        // karna ini diproses di js jadi return nya juga bentuk respons JS (json)
        return response()->json([
            'message' => 'berhasil membuat data tiket!',
            'data' => $createData
        ]);
    }

    public function ticketOrderPage($ticketId) {
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.cinema', 'schedule.movie'])->first();
        $promos = Promo::where('actived', 1)->get();
        return view('schedule.order', compact('ticket', 'promos'));
    }

    public function createBarcode(Request $request) {
        $kodeBarcode = 'TICKET' . $request->ticket_id;

        $qrImage = QrCode::format('svg')
            ->size(300) //ukuran pixel
            ->margin(2) //margin tepi
            ->errorCorrection('H') //tingkat koreksi error : L, M, Q, H
            ->generate($kodeBarcode);

        $filename = $kodeBarcode . '.svg';
        $path = 'barcodes/' . $filename;

        Storage::disk('public')->put($path, $qrImage);

        $createData = TicketPayment::create([
            'ticket_id' => $request->ticket_id,
            'qrcode' => $path,
            'status' => 'process',
            'booked_date' => now(),
        ]);

        $ticket = Ticket::find($request->ticket_id);
        $totalPrice = $ticket->total_price;
        if ($request->promo_id != NULL) {
            $promo = Promo::find($request->promo_id);
            if ($promo['type'] == 'percent') {
                $discount = $ticket['total_price'] * $promo['discount'] / 100;
            } else {
                $discount = $promo['discount'];
            }
            $totalPrice = $ticket['total_price'] - $discount;
        }
        $updateTicket = Ticket::where('id', $request->ticket_id)->update([
            'promo_id' => $request->promo_id,
            'total_price' => $totalPrice
        ]);

        return response()->json([
            'message' => 'berhasil membuat pesanan tiket sementara!',
            'data' => $createData
        ]);
    }

    public function ticketPaymentPage($ticketId) {
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'promo', 'ticketPayment'])->first();
        return view('schedule.payment', compact('ticket'));
    }

    public function updateStatusTicket($ticketId) {
        $updatePayment = TicketPayment::where('ticket_id', $ticketId)->update(['paid_date' => now()]);
        $updateStatus = Ticket::where('id', $ticketId)->update(['actived' => 1]);
        // diarahkan ke halaman route tickets.show untuk munculin tiket
        return redirect()->route('tickets.show', $ticketId);
    }

    /**
     * Display the specified resource.
     */
    public function show($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.movie', 'schedule.cinema', 'ticketPayment'])->first();
        return view('schedule.ticket', compact('ticket'));
    }

    public function exportPdf($ticketId) {
        // siapkan data yang akan ditampilkan di pdf, hasilnya harus bentuk array (toArray())
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.movie', 'schedule.cinema', 'ticketPayment'])->first()->toArray();
        // buat naam variabel yang akan digunakan di blade pdf
        view()->share('ticket', $ticket);
        // menentukan file blade yang akan dicetak dan kirim juga datanya
        $pdf = Pdf::loadView('schedule.export-pdf', $ticket);
        // download pdf dengan nama tertentu
        $fileName = 'TICKET' . $ticketId . '.pdf';
        return $pdf->download($fileName);
    }

    public function dataChart() {
        // mengambil 
        $month = now()->format('m');
        // hasil collection (get), dikelompokkan berdasarkan booked_date
        // toArray() : ubah collection menjadi array untuk memudahkan pengambilan data
        $tickets = Ticket::where('actived', 1)->whereHas('ticketPayment', function($q) use($month) {
            $q->whereMonth('booked_date', $month);
        })->get()->groupBy(function($ticket) {
            return Carbon::parse($ticket->ticketPayment->booked_date)->format('Y-m-d');
        })->toArray();
        // dd($tickets);
        // ambil key dari array assoc (tanggal)
        $labels = array_keys($tickets);
        //  siapkan wadah untuk array yang akan berisi angka-angka jumlah data di tanggal tersebut
        $data = [];
        foreach ($tickets as $ticketGroup) {
            array_push($data, count($ticketGroup));
        }
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
