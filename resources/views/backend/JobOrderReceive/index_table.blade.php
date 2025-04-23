<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 action-buttons">
            <a class="btn btn-info waves-effect waves-light" href="{{ route('job-orders-receive.csv') }}">Export CSV</a>
        </div>
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Job Order No.</th>
                    <th>Party</th>
                    <th>Party Bill Info</th>
                    <th style="width: 15%">Amount Info</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td class="text-center">{{ $record->id }}</td>
                    <td>{{ $record->jobOrders->order_no }}</td>
                    <td>{{ $record->party->name }}</td>
                    <td>
                        Party Challan No. : {{ $record->challan_no }}
                        <br />
                        Receive Date : {{ if_date($record->receive_date) }}
                        <br />
                    </td>
                    <td>
                        Amount : {{ $record->amount }}
                        <br />
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

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
                                    <th>Sent Item</th>
                                    <th>Sent Item</th>
                                    <th>Receive Item</th>
                                    <th>Receive Qty</th>
                                    <th>Comments</th>
                                    <th>Receive Warehouse</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->jobOrderReceiveItem as $k => $job_receive_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $job_receive_item->jobOrderItem->fromItem->name   }}</td>
                                    <td>
                                        {{ $job_receive_item->jobOrderItem->from_qty }}
                                        {{ $job_receive_item->jobOrderItem->fromItem->unit->code   }}
                                    </td>
                                    <td>{{ $job_receive_item->toItem->name }}</td>
                                    <td>
                                        {{ $job_receive_item->to_qty }}
                                        {{ $job_receive_item->toItem->unit->name }}
                                    </td>
                                    <td>{{ $job_receive_item->comments }}</td>
                                    <td>
                                        @if($job_receive_item->receive_warehouse_id)
                                        {{ $job_receive_item->warehouse->name }}
                                        @else
                                        {{ $job_receive_item->party->name ?? null }}
                                        @endif
                                    </td>
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