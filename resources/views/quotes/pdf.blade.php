<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previsualización de Cotización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        .quote-header {
            margin-bottom: 20px;
        }
        .quote-details, .quote-lines {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .quote-details th, .quote-lines th, .quote-details td, .quote-lines td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .quote-details th, .quote-lines th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="quote-header">
        <h1>Previsualización de la Cotización</h1>
        <p><strong>Cotización ID:</strong> {{ $quote->id }}</p>
        <p><strong>Cliente:</strong> {{ $quote->customer->name }}</p>
        <p><strong>Fecha:</strong> {{ $quote->created_at->format('d/m/Y') }}</p>
    </div>

    <h2 style="margin-top:40px;">Detalle de Ítems</h2>
    <table style="width:100%; border-collapse:collapse; font-size:14px;">
        <thead>
            <tr style="background:#f2f2f2;">
                <th style="border:1px solid #000; padding:8px;">#</th>
                <th style="border:1px solid #000; padding:8px;">Ítem</th>
                
                <th style="border:1px solid #000; padding:8px;">Cantidad</th>
                <th style="border:1px solid #000; padding:8px;">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->details as $detail)
                <tr style="background:#e9f7ef; font-weight:bold;">
                    <td style="border:1px solid #000; padding:8px;">{{ $detail->order }}</td>
                    <td style="border:1px solid #000; padding:8px;">{{ $detail->item }}</td>
                    
                    <td style="border:1px solid #000; padding:8px;">{{ $detail->quantity ?? '' }}</td>
                    <td style="border:1px solid #000; padding:8px;text-align:right">$ {{ number_format($detail->calculateAmount(), 0, ',', '.') }}</td>
                </tr>
                @if ($detail->lines->count())
                    @foreach ($detail->lines as $line)
                        <tr style="background:#f9f9f9;">
                            <td style="border:1px solid #000; padding:8px;">{{ $detail->order }}.{{ $loop->iteration }}</td>
                            <td style="border:1px solid #000; padding:8px; padding-left:20px;">{{ $line->product->name ?? '' }}</td>
                            
                            <td style="border:1px solid #000; padding:8px;text-align:right">{{ $line->quantity }}</td>
                            <td style="border:1px solid #000; padding:8px;text-align:right">$ {{ number_format($line->sale_value, 0, ',', '.') }}</td>
                        </tr>
                        @if (isset($line->subitems) && count($line->subitems))
                            @foreach ($line->subitems as $subitem)
                                <tr style="background:#fff;">
                                    <td style="border:1px solid #000; padding:8px;">{{ $detail->order }}.{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                    <td style="border:1px solid #000; padding:8px; padding-left:40px;">{{ $subitem->item ?? '' }}</td>
                                    
                                    <td style="border:1px solid #000; padding:8px;">{{ $subitem->quantity ?? '' }}</td>
                                    <td style="border:1px solid #000; padding:8px;">{{ isset($subitem->amount) ? number_format($subitem->amount, 2, ',', '.') . ' €' : '' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

</body>
</html>