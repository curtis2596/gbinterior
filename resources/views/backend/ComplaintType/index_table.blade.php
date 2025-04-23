<?php

// use App\Models\LedgerAccount;
?>

<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">


        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th><?= sortable_anchor('name', 'Name') ?></th>
                    <th>Active / De-Active</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>

                        <td>{{ $record->name }}</td>
                        <td>
                            <x-backend.active-deactive :isActive="$record->is_active" :routePrefix="$routePrefix" :id="$record->id" />
                        </td>
                        <td>
                            <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                        </td>
                        <td>
                            <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
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
