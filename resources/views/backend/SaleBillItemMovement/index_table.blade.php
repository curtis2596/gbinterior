<div class="card summary-card">

    <div class="card-header">
        Summary
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Item</th>
                    <th>Warehouse</th>
                    <th>Qty</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 8%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td class="text-center">{{ $record->id }}</td>
                    <td>
                        {{ $record->saleBillItem->item->name }}
                        <br/>
                        SKU : {{ $record->saleBillItem->item->sku }}
                    </td>
                    <td>{{ $record->warehouse->name }}</td>
                    <td>
                        {{ $record->qty }} {{ $record->saleBillItem->item->unit->code }}
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-delete-button url="{{ route($routePrefix . '.destroy', [$record->id]) }}"/>
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