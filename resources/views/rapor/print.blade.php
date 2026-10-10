<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor {{ $siswa->nama }}</title>
    @php
        // Set locale ke Indonesia untuk format tanggal
        \Carbon\Carbon::setLocale('id');
    @endphp
    @include('rapor.partials.lembar-style')
</head>

<body onload="window.print()">
    @include('rapor.partials.lembar')
</body>

</html>
