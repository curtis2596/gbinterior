
<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">

   

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Challan No</th>
                    <th>Challan Date</th>
                    <th>From Warehouse</th>
                    <th>To Warehouse</th>
                    <th>From Item</th>
                    <th>To Item</th>
                    <th>From Quantity</th>
                    <th>To Quantity</th>
                    <th>Process</th>
                    <th>Wastage</th>
                    <th>Amount</th>
                    <th>Comments</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->challan_no }}</td>
                    <td>{{ if_date($record->challan_date) }}</td>
                    <td>{{ $record->fromwarehouse->name }}</td>
                    <td>{{ $record->towarehouse->name }}</td>
                    <td>{{ $record->fromitem->name }}</td>
                    <td>{{ $record->toitem->name }}</td>
                    <td>{{ $record->from_qty }}</td>
                    <td>{{ $record->to_qty }}</td>
                    <td>{{ $record->process->name ?? null }}</td>
                    <td>{{ $record->wastage_qty }}</td>
                    <td>{{ $record->amount }}</td>
                    <td>{{ $record->comments }}</td>
                    
                    <td>
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
        <x-Backend.pagination-links :records="$records" />
    </div>
</div>