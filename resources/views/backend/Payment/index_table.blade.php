<div class="card summary-card">
        
    <div class="card-header">
        <x-Backend.pagination-links :records="$records"/>
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 action-buttons">
            <a class="btn btn-info waves-effect waves-light" href="{{ url(route($routePrefix . '.csv', $search)) }}">Export CSV</a>
        </div>

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>From Account</th>
                    <th>To Account</th>
                    <th>Voucher No.</th>
                    <th><?= sortable_anchor('voucher_date', 'Date') ?></th>
                    <th><?= sortable_anchor('amount', 'Amount') ?></th>
                    <th>Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->fromAccount->getDisplayName() }}</td>
                    <td>{{ $record->toAccount->getDisplayName() }}</td>
                    <td>{{ $record->voucher_no }}</td>
                    <td>{{ $record->voucher_date }}</td>
                    <td>
                        {{ $record->amount }}
                        @if($record->is_advance_pay)
                            <br/>
                            <span class="badge rounded-pill bg-info text-bg-info">Advance Pay</span>
                            <br/>
                        @endif
                    </td>                   
                    <td>
                        Narration : {{ $record->narration }}
                        <br/>
                        Transaction No. : {{ $record->bank_transaction_no }}
                        <br/>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" buttons="delete" />                        
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <x-Backend.pagination-links :records="$records"/>
    </div>
</div>