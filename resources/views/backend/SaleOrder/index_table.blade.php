<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Voucher No.</th>
                    <th>Party Info</th>
                    <th>Delivery</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->voucher_no }}</td>
                    <td>
                        Party : {{ $record->party->name }}
                        <br/>
                        Party Order No. : {{ $record->party_order_no }}
                        <br/>
                        Party Order Date : {{ if_date($record->order_date) }}
                        <br/>
                    </td>
                    <td>
                        Expected Delivery Date : {{ if_date($record->expected_delivery_date) }}
                        <br/>
                        Pending Qty : {{ $record->pending_qty }}
                        <br/>
                        Total Amount : {{ $record->total_amount }}
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
                                    <th>Item</th>
                                    <th>Demand Qty</th>
                                    <th>Sent Qty</th>
                                    <th>Unit</th>
                                    <th>Expected Rate</th>
                                    <th>Amount</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->saleOrderItem as $k => $sale_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $sale_item->item->name }}</td>
                                    <td>{{ $sale_item->required_qty }}</td>
                                    <td>{{ $sale_item->sent_qty }}</td>
                                    <td>{{ $sale_item->item->unit?->code }}</td>
                                    <td>{{ $sale_item->rate }}</td>
                                    <td>{{ $sale_item->amount }}</td>
                                    <td>{{ $sale_item->comments }}</td>
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