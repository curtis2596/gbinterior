@extends('layouts.pdf')
@section('content')
<main class="container">


    <table class="table table-borderless" style="vertical-align: middle;">
       <tbody>
           <tr>
               <td style="border-width: 0;">
                   <p style="font-size: 3rem;">INVOICE</p>
                   <p class="mb-4">
                    <span class="fw-bold">Invoice Date:</span> {{ date('d M, Y', strtotime($data['payment_date'])) }} 
                </p>
               </td>
               <td class="text-right" style="border-width: 0;">
                    <img src="{{ public_path('assets/img/bmc.png') }}" alt="BMC" width="80">
               </td>
           </tr>
       </tbody>
    </table>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>COMPANY INFO</th>
                <th>INVOICE TO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%">
                    <p>{{ config('app.company_name') }}</p>
                    <p>{{ config('app.company_address') }}</p>
                    <p>{{ config('app.company_contacts') }}</p>
                </td>
                <td width="50%">
                    <p>{{ $order->party->name }}</p>
                    <p>{{ $order->party->address ?? 'NA' }}</p>
                    <p>{{ $order->party->phone }}</p>
                </td>
            </tr>
        </tbody>
    </table>


    <table class="table table-bordered  mb-4" style="min-width: 30rem">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Item</th>
                <th>Rate</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items_id'] as $i => $item_id)
            @php
            $orderItem = $order->orderItems->find($item_id);
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $orderItem->item->name }}</td>
                <td>{{ $orderItem->rate }}</td>
                <td>{{ $data['current_quantities'][$i] }}</td>
                <td>{{ $orderItem->rate * $data['current_quantities'][$i] }}</td>
            </tr>
            @if ($loop->last)
            <tr>
                <td colspan="3"></td>
                <td>Amount</td>
                <td>{{ $data['amount'] }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td>GST</td>
                <td>{{ $data['gst_amount'] }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td>Extra Charges</td>
                <td>{{ $data['extra_charges'] }}</td>
            </tr>
            <tr class="fw-bold">
                <td colspan="3"></td>
                <td>Total Amount</td>
                <td>
                    {{-- {{ number_format(20, 2, '.', '') }} --}}
                    {{ number_format($data['payment_amount'], 2, '.', '') }}
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <strong>Amount in words:</strong>
                    @php
                    $number_formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                    $amount_in_words = ucwords(str_replace('-', ' ',
                    $number_formatter->format($data['payment_amount']))). ' Only';
                    @endphp
                    <p>{{ $amount_in_words }}</p>
                </td>
            </tr>
            @endif

            @endforeach
        </tbody>
    </table>


    <footer class="mb-5">
        <p class="mb-4 fow-bold">{{ config('app.company_name') }}</p>
        <p>Authorized</p>
    </footer>

</main>

@endsection