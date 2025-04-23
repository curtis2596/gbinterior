@extends('layouts.dashboard')
@section('content')


<header class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <h5>Ledger <span class="badge bg-primary ms-1 fw-normal">page</span></h5>
    <div>
        <button onclick="return window.print();" class="btn btn-sm btn-success text-white me-1">Print</button>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary">Back</a>
    </div>
</header>

@include('ledger.accordion')

@php
    $type = request('type');
    $party_id = request('party_id');
@endphp

{{-- account ledger --}}
<section class="table-responsive">

    @isset($account_transactions)
    <table class="table table-bordered align-middle" style="min-width: 40rem;" id="account-transactions">
        <thead>
            <tr>
                <th colspan="7" class="text-center">
                    <i class="fa fa-circle text-success me-1"></i> Account Ledger
                </th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Particlar</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
                <th>DR/CR</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account_transactions as $transaction)
            <tr>
                <td>{{ date('d M, Y', strtotime($transaction->transaction_date)) }}</td>
                <td>
                    @if (empty($transaction->creditor_id))
                    {{ $transaction->narration }}

                    @else
                    <a href="{{ route('account-vouchers.edit', ['transaction' => $transaction->id]) }}"
                        target="_blank" class="mb-1 d-inline-block">{{ $transaction->party_name }}
                    </a>

                    <span class="badge ms-1 {{ $transaction->is_paid ? 'bg-success' : 'bg-danger' }}">
                        {{ $transaction->is_paid ? 'Paid' : 'Unpaid' }}
                    </span>

                        @if(isset($transaction->order_id))
                          - <a href="{{ url("orders/{$transaction->order_id}") }}" target="_blank" class="d-inline-block mb-1">Order</a>
                        @endif

                        @if(isset($transaction->transfer_id))
                          - <a href="{{ url("transfers/{$transaction->transfer_id}") }}" target="_blank" class="d-inline-block mb-1">Transfer</a>
                        @endif

                    <p style="max-width:24rem;" class="overflow-auto mb-1">{{ $transaction->narration }}</p>
                    @endif
                </td>
                <td>{{ $transaction->amt_debt }}</td>
                <td>{{ $transaction->amt_credit }}</td>
                <td>
                    @php
                    $balance += ($transaction->amt_credit - $transaction->amt_debt)
                    @endphp
                    {{ number_format(abs($balance), 2, '.', '') }}
                </td>
                <td>
                    @if ($balance > 0)
                    Cr
                    @elseif($balance < 0)
                    Dr
                    @elseif($balance == 0)
                    -
                    @endif
                </td>
                <td>
                    @can('delete', App\Models\Transaction::class)

                    @if (!$transaction->is_paid)
                        <form action="{{ route('transactions.mark') }}"
                            method="POST" onsubmit="return confirm('Are You Sure?')"
                            class="d-inline-block">
                            @csrf
                            <input type="hidden" name="transaction_date" value="{{  $transaction->transaction_date }}">
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="party_id" value="{{ $party_id }}">
                            <button class="btn btn-sm text-primary" type="submit">
                                <i class="fa fa-check-square"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('account-vouchers.destroy', ['transaction' => $transaction->id]) }}"
                        method="POST" onsubmit="return confirm('Are You Sure?')"
                        class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit">
                            <i class="fa fa-trash-alt text-primary"></i>
                        </button>
                    </form>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <footer class="mb-4 mb-lg-5">
     {{ $account_transactions->links() }}
    </footer>
    @endisset

    {{-- stock ledger --}}
    @isset($stock_transactions)
    <table class="table table-bordered align-middle" style="min-width: 40rem;" id="stock-transactions">
        <thead>
            <tr>
                <th colspan="7" class="text-center">
                    <i class="fa fa-circle text-success me-1"></i> Stock Ledger
                </th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Particlar</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
                <th>DR/CR</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach ($stock_transactions as $transaction)
        <tr>
            <td>{{ date('d M, Y', strtotime($transaction->transaction_date)) }}</td>
            <td>
                @if (empty($transaction->creditor_id))
                 {{ $transaction->narration }}
                @else
                <a href="{{ route('stock-vouchers.edit', ['transaction' => $transaction->id]) }}"
                    target="_blank" class="mb-1 d-inline-block">{{ $transaction->party_name }}
                </a>

                <span class="badge ms-1 {{ $transaction->is_paid ? 'bg-success' : 'bg-danger' }}">
                    {{ $transaction->is_paid ? 'Paid' : 'Unpaid' }}
                </span>

                <p style="max-width:24rem;" class="overflow-auto mb-1">{{ $transaction->narration }}</p>
                @endif
            </td>
            <td>{{ $transaction->stock_debt }}</td>
            <td>{{ $transaction->stock_credit }}</td>
            <td>
                @php
                $balance += ($transaction->stock_credit - $transaction->stock_debt)
                @endphp
                {{ number_format(abs($balance), 2, '.', '') }}
            </td>
            <td>
                @if ($balance > 0)
                Cr
                @elseif($balance < 0)
                Dr
                @elseif($balance == 0)
                -
                @endif
                </td>
                <td>
                    @can('delete', App\Models\Transaction::class)

                    @if (!$transaction->is_paid)
                        <form action="{{ route('transactions.mark') }}"
                            method="POST" onsubmit="return confirm('Are You Sure?')"
                            class="d-inline-block">
                            @csrf
                            <input type="hidden" name="transaction_date" value="{{  $transaction->transaction_date }}">
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="party_id" value="{{ request('party_id') }}">
                            <button class="btn btn-sm text-primary" type="submit">
                                <i class="fa fa-check-square"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('stock-vouchers.destroy', ['transaction' => $transaction->id]) }}"
                        method="POST" onsubmit="return confirm('Are You Sure?')"
                        class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm" type="submit">
                            <i class="fa fa-trash-alt text-primary"></i>
                        </button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <footer class="mb-4 mb-lg-5">
        {{ $stock_transactions->links() }}
    </footer>
    @endisset
</section>
@endsection


@push('scripts')
<script>

    $(document).ready(() => {

      $('select').selectize();

    });


</script>
@endpush
