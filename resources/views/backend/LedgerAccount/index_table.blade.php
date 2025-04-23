<?php

use App\Models\LedgerAccount;

$balance_types = laravel_constant("balance_types");
?>

<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 action-buttons">
            <a class="btn btn-info waves-effect waves-light" href="{{ url(route($routePrefix . '.csv', $search)) }}">Export CSV</a>
        </div>

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Category</th>
                    <th><?= sortable_anchor('name', 'Account') ?></th>
                    <th><?= sortable_anchor('opening_balance', 'Opening Balance') ?></th>
                    <th><?= sortable_anchor('current_balance', 'Current Balance') ?></th>
                    <th>Net Balance</th>
                    <th>Active / De-Active</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>
                        {{ $ledgerCategoryList[$record->ledger_category_id] ?? "" }}
                    </td>
                    <td>{{ $record->getDisplayName() }}</td>                    
                    <td>{{ $record->getOpeningBalanceWithCrOrDr() }}</td>
                    <td>{{ $record->getCurrentBalanceWithCrOrDr() }}</td>
                    <td>{{ $record->getBalanceWithCrOrDr() }}</td>
                    <td>
                        @if(!$record->is_pre_defined)
                            <x-backend.active-deactive :isActive="$record->is_active" :routePrefix="$routePrefix" :id="$record->id" />
                        @endif
                    </td>
                    <td>
                        @if(!$record->is_pre_defined)
                            <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                        @endif
                    </td>
                    <td>
                        @if($record->is_pre_defined)
                            <span class="badge bg-success">Pre-Defined</span>
                            <span class="badge bg-info">Pre-Defined Records can not edit or Delete</span>
                        @else
                            <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <x-Backend.pagination-links :records="$records" />
    </div>
</div>