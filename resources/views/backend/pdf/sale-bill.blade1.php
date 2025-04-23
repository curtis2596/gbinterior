@extends('layouts.pdf')
@section('content')
<main class="container">
    @php
        $transaction_date="2022-08-09";
        $wa_narration="NA";
    @endphp
    @foreach($transactiondata as $transaction)
        @php 
            @$transaction_date = $transaction->transaction->transaction_date;
            @$wa_narration = $transaction->transaction->wa_narration;
        @endphp
    @endforeach
    <table class="table table-borderless" style="vertical-align: middle;">
       <tbody>
           <tr>
               <td style="border-width: 0;">
                   <p style="font-size: 3rem;">INVOICE</p>
                   <p class="mb-4">
                    <span class="fw-bold">Invoice Date:</span> 
                    {{ date('d M, Y', strtotime($transaction_date)) }} 
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
            @php
                $totalsum =0;
            @endphp
            @foreach($transactiondata as $transaction) 
            @php  
                if($transaction->type == 'sale'){ 
                    $curr_val = (float)$transaction->transaction->amt_credit -(float)@$transaction->transaction->gst_amount-(float)@$transaction->transaction->extra_charges;  
                }else{ 
                    $curr_val = (float)$transaction->transaction->amt_debt -(float)@$transaction->transaction->gst_amount-(float)@$transaction->transaction->extra_charges;
                }
                $totalsum += (float)$transaction->transaction->amt_debt;  
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ @$transaction->item->name }}</td>
                <td>{{ @$transaction->rate ? abs((float)$transaction->rate) :'' }}</td>
                <td>{{ @$transaction->rate ? abs((float)$transaction->quantity) :'' }}</td>
                <td>{{ @$transaction->rate ? abs((float)$transaction->rate * (float)$transaction->quantity ) :''}}</td>
            </tr>
            @if ($loop->last)
            <tr>
                <td colspan="3"></td>
                <td>Amount</td>
                <td>{{ abs((float)$curr_val) }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td>GST</td>
                <td>{{ (float)$transaction->transaction->gst_amount }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td>Extra Charges</td>
                <td>{{ (float)$transaction->transaction->extra_charges }}</td>
            </tr>
            <tr class="fw-bold">
                <td colspan="3"></td>
                <td>Total Amount</td>
                <td>
                    <?php
                        $grandtotal = (float)$curr_val + (float)$transaction->transaction->gst_amount + (float)$transaction->transaction->extra_charges;
                    ?>
                    {{ number_format(abs($grandtotal ), 2, '.', '') }}
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <strong>Narration</strong>
                    @php
                    // $number_formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                    // $amount_in_words = ucwords(str_replace('-', ' ',$number_formatter->format($transaction->transaction->amt_debt))). ' Only';
                    @endphp
                    <p>{{ $msg ?? '' }}</p>
                </td>
            </tr>
            {{-- <tr>
                <td colspan="5">
                    <strong>Amount in words:</strong>
                    @php
                    $number_formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                    $amount_in_words = ucwords(str_replace('-', ' ',$number_formatter->format($transaction->transaction->amt_debt))). ' Only';
                    @endphp
                     <p>{{ $amount_in_words }}</p>
                </td>
            </tr> --}}
            @endif

            @endforeach
        </tbody>
    </table>


    {{-- <footer class="mb-5">
        <p class="mb-4 fow-bold">Narration</p>
        <p>{{$wa_narration}}</p>
    </footer> --}}
    <footer class="mb-5">
        <p class="mb-4 fow-bold">{{ config('app.company_name') }}</p>
        <p>Authorized</p>
    </footer>

</main> 

@endsection