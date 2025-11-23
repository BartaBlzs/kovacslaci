<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Megyék Lista</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; border-top: 1px solid #000; padding-top: 5px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Magyar Megyék Listája</h1>
        <p>Generálva: {{ date('Y-m-d H:i:s') }}</p>
    </div>
    
    <!-- Content -->
    <table>
        <thead>
            <tr>
                <th>Megye neve</th>
                <th>Városok száma</th>
            </tr>
        </thead>
        <tbody>
            @foreach($counties as $county)
            <tr>
                <td>{{ $county->name }}</td>
                <td>{{ $county->cities->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top: 20px;"><strong>Összesen:</strong> {{ $counties->count() }} megye</p>
    
    <!-- Footer -->
    <div class="footer">
        <p>Magyar Városok és Megyék Nyilvántartás</p>
    </div>
</body>
</html>