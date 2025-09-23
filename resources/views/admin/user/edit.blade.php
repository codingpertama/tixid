<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TIXID</title>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.min.css" rel="stylesheet" />
</head>
    
<body>
<div class="container mt-5">
    <nav aria-label="breadcrumb" class="card shadow-sm p-3 bg-body rounded">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Pengguna</li>
                <li class="breadcrumb-item">Data</li>
                <li class="breadcrumb-item">Edit</li>
            </ol>
    </nav>
    <form class="w-100 d-block mx-auto my-3 card shadow-sm rounded p-3 bg-body" method="POST" action="{{ route('admin.users.update', $user['id']) }}">
        <h5 class="text-center">Ubah Data Staff</h5>
        @csrf
        @method('PUT')
        @error('name')
            <small class="text-danger">{{$message}}</small>
        @enderror
        <div data-mdb-input-init class="mb-3">
            <label for="form1Example1" class="form-label">Nama lengkap</label>
            <input type="text" id="form1Example1" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user['name'] }}">
        </div>
        @error('email')
            <small class="text-danger">{{$message}}</small>
        @enderror
        <div data-mdb-input-init class="mb-3">
            <label for="form1Example1" class="form-label">Email</label>
            <input type="email" id="form1Example1" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user['email'] }}">
        </div>
        @error('password')
            <small class="text-danger">{{$message}}</small>
        @enderror
        <div data-mdb-input-init class="mb-3">
            <label for="form1Example2" class="form-label">Password</label>
            <input type="password" id="form1Example2" class="form-control @error('password') is-invalid @enderror" name="password">
        </div>
        <button data-mdb-input-init type="submit" class="btn btn-primary btn-block">Edit Data</button>
        <div class="text-center mt-3">
            <a href="{{ route('admin.users.index') }}">Kembali</a>
        </div>
    </form>
</div>


    <!-- MDB -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/9.1.0/mdb.umd.min.js"></script>
    {{-- script bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous">
    </script>
</body>

</html>