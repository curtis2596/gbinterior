<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">



        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Challan No.</th>
                    <th><?= sortable_anchor('challan_date', 'Date') ?></th>
                    <th>From</th>
                    <th>To</th>
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
                    <td>{{ $record->fromWarehouse->name }}</td>
                    <td>{{ $record->toWarehouse->name }}</td>
                    <td>{{ $record->comments }}</td>

                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" buttons="delete" />
                        <br /><br />

                        <span class="btn btn-info btn-sm css-toggler mb-1"
                            data-sr-css-class-toggle-target="#record-{{ $record->id }}" data-sr-css-class-toggle-class="hidden">
                            Details
                        </span>
                    </td>
                </tr>
                <tr id="record-{{ $record->id }}" class="hidden">
                    <td></td>
                    <td colspan="6">
                        <h4>Items</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->warehouseinventoryitem as $k => $warehouse_movement_items)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $warehouse_movement_items->Item->name ?? Null}}</td>
                                    <td>{{ $warehouse_movement_items->qty }}</td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
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