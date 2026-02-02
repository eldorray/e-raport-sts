<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Guru</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e5e7eb;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Data Guru</h1>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 30%">NIP/NUPTK</th>
                <th style="width: 45%">Nama Guru</th>
                <th style="width: 20%">Password</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gurus as $index => $guru)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $guru->nip }}</td>
                <td>{{ $guru->nama }}</td>
                <td>{{ $guru->initial_password ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
