<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table,
        .table th,
        .table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .table-borderless td {
            border: none;
        }

        .text-center {
            text-align: center;
        }

        .mb-4 {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <header class="header">

    </header>

    <h6 style="border-bottom: 1px solid #000; padding: 10px;">
        Quotation Info
    </h6>

    <table class="table">
        <tbody>
            <tr>
                <td>
                    <b>Date:</b> {{ $quotation->date }} <br>
                    @if($quotation->is_new)
                    <b>Customer Name:</b> {{ $quotation->customer_name }} <br>
                    <b>Customer Email:</b> {{ $quotation->customer_email }}
                    @else
                    <b>Party:</b> {{ $quotation->party->name ?? 'N/A' }}
                    @endif
                    <br>
                </td>
            </tr>
        </tbody>
    </table>

    <h6 style="border-bottom: 1px solid #000; padding: 10px;">
        Items Info
    </h6>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price Per Unit</th>
                <th>Min. Qty To Purchase</th>
                <th>Extra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->Item->name ?? 'N/A' }}</td>
                <td>{{ $item['price'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ $item['amount'] }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table table-borderless">
        <tbody>
            <tr>
                <td><strong>Authorized:</strong></td>
            </tr>
        </tbody>
    </table>

    <footer class="footer">
        <table class="table table-borderless">
            <tbody>
                <tr>
                   
                </tr>
            </tbody>
        </table>
    </footer>
</body>

</html>