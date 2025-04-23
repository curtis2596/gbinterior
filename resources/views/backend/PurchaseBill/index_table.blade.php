<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Voucher No.</th>
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
                    <td>{{ $record->voucher_no }}</td>
                    <td>{{ $record->party->name }}</td>
                    <td>
                        Party Bill No. : {{ $record->party_bill_no }}
                        <br/>
                        Party Bill Date : {{ $record->bill_date }}
                        <br/>
                    </td>
                    <td>
                        Amount : {{ $record->amount }}
                        <br/>
                        Freight : {{ $record->freight }}
                        <br/>
                        Discount : {{ $record->discount }}
                        <br/>
                        IGST : {{ $record->igst }}
                        <br/>
                        SGST : {{ $record->sgst }}
                        <br/>
                        CGST : {{ $record->cgst }}
                        <br/>
                        Payable Amount : {{ $record->payable_amount }}
                        <br/>
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br/><br/>

                        <a href="{{ route('purchase-bill-item-movement.index', ['purchase_bill_id' => $record->id]) }}" 
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Move Items To Warehouse
                        </a>

                        <br/><br/>

                        <a href="{{ route($routePrefix . '.return_items', ['id' => $record->id]) }}" 
                            class="btn btn-secondary btn-sm summary-action-button">
                            <i class="fas fa-suitcase"></i> Return
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
                    <td colspan="6">
                        <h4>Items</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Rate</th>
                                    <th>IGST</th>
                                    <th>SGST</th>
                                    <th>CGST</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->purchaseBillItem as $k => $purchase_bill_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $purchase_bill_item->item->getDisplayName() }}</td>
                                    <td>{{ $purchase_bill_item->qty }}</td>
                                    <td>{{ $purchase_bill_item->item->unit?->getDisplayName(); }}</td>
                                    <td>{{ $purchase_bill_item->rate }}</td>
                                    <td>
                                        {{ $purchase_bill_item->igst }} ({{ $purchase_bill_item->igst_per }}%)
                                    </td>
                                    <td>
                                        {{ $purchase_bill_item->sgst }} ({{ $purchase_bill_item->sgst_per }}%)
                                    </td>
                                    <td>
                                        {{ $purchase_bill_item->cgst }} ({{ $purchase_bill_item->cgst_per }}%)
                                    </td>
                                    <td>{{ $purchase_bill_item->amount }}</td>
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