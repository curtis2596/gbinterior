@extends($layout)

@section('content')

<?php

use App\Helpers\DateUtility;

?>

@include($partial_path . ".page_header")


<div class="card">
    <div class="card-body">
        <form method="GET">
            <div class="row mb-4">
                <div class="col-md-6">
                    <x-Inputs.drop-down name="item_id" label="Item"
                        :value="$search['item_id']"
                        :list="$item_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="warehouse_id" label="Warehouse"
                        :value="$search['warehouse_id']"
                        :list="$warehouse_list"
                        class="form-control select2" />
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                    <div>
                        <button type="submit" class="btn btn-primary w-md">Search</button>
                        <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


@if (isset($records))
<div class="card mt-3">
    <div class="card-body">
        <div class="mt-2 mb-2">
            <span class="btn btn-secondary table-export-csv"
                data-sr-table-csv-export-target="#report"
                data-sr-table-csv-export-filename="Item-current-stock">
                Export CSV (JS)
            </span>
        </div>

        <table class="table table-striped table-bordered table-hover mb-0" id="report">
            <thead>
                <tr>
                    <th>Parent Category</th>
                    <th>Child Category</th>
                    <th>Item Group</th>
                    <th>Brand</th>
                    <th>Item</th>
                    <th>Warehouse</th>
                    <th>Available Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 0; ?>
                @foreach($records as $record)
                <?php $counter++; ?>
                <tr>
                    <td>{{ $record->item->itemCategory->parent?->name }}</td>
                    <td>{{ $record->item->itemCategory->name }}</td>
                    <td>{{ $record->item->itemGroup->name }}</td>
                    <td>{{ $record->item->brand->name }}</td>
                    <td>{{ $record->item->name }}</td>
                    <td>{{ $warehouse_list[$record['warehouse_id']] }}</td>
                    <td>{{ $record->getAvailabilitQty() }}</td>
                    <td>{{ $record->item->unit->code }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection