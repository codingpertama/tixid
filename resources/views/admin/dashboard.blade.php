@extends('templates.app')

@section('content')
    <div class="container mt-3">
        <h5>Grafik Pembelian Tiket</h5>
        @if (Session::get('success'))
            <div class="alert alert-success">{{Session::get('success')}} <b>Selamat datang, {{Auth::user()->name}}</b></div>
        @endif
        <div class="row mt-5">
            <div class="col-6">
                <canvas id="chartBar"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        let labels = null;
        let data = null;

        $(function() {
            $.ajax({
                url: "{{ route('admin.tickets.chart') }}",
                method: "GET",
                success: function(response) {
                    labels = response.labels;
                    data = response.data;
                    chartBar();
                },
                error: function(err) {
                    alert("gagal mengambil data untuk grafik!");
                }
            })
        });

        const ctx = document.getElementById('chartBar');
        function chartBar() {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Penjualan Tiket Bulan Ini',
                        data: data,
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endpush