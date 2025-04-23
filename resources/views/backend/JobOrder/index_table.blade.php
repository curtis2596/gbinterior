<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Order No.</th>
                    <th>Party Info</th>
                    <th>Expected Complete Date</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->order_no }}</td>
                    <td>{{ $record->party->name }}</td>
                    <td>
                        {{ if_date($record->expected_complete_date) }}
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br/><br/>
                        
                        <a class="btn btn-secondary summary-action-button" href="{{ route($routePrefix . '.print',[$record->id]) }}">
                            <i class="fas fa-print"></i> Print
                        </a>

                        <br/><br/>

                        <span class="btn btn-info btn-sm css-toggler mb-1"
                            data-sr-css-class-toggle-target="#record-{{ $record->id }}" data-sr-css-class-toggle-class="hidden"
                        >
                            Details
                        </span>
                    </td>
                </tr>
                <tr id="record-{{ $record->id }}" class="hidden">
                    <td></td>
                    <td colspan="5">
                        <h4>Items</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Send Item</th>
                                    <th>Send Qty</th>
                                    <th>Receive Item</th>
                                    <th>Receive Qty</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->JobOrderItem as $k => $job_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $job_item->fromItem->getDisplayName() }}</td>
                                    <td>{{ $job_item->from_qty }}</td>
                                    <td>{{ $job_item->toItem->getDisplayName() }}</td>
                                    <td>{{ $job_item->to_qty }}</td>
                                    <td>{{ $job_item->comments }}</td>
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