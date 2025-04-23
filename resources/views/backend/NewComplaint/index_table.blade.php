<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%"><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Complaint No.</th>
                    <th>Complaint Date</th>
                    <th>Customer name</th>
                    <th>Status</th>
                    <th>Is Free</th>
                    <th>Assign to</th>
                    <th>Remarks</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td class="text-center">{{ $record->id }}</td>
                    <td>{{ $record->complaint_no }}</td>
                    <td>{{ if_date($record->date) }}</td>
                    <td>
                        {{ $record->party->name }} <br>
                        Contact No. : {{ $record->contact_number }} <br>
                        Contact Person : {{ $record->contact_person }}
                    </td>
                    <td>{{ $record->status }}</td>
                    <td>
                        @if($record->is_free === 0)
                        Amount : {{ $record->amount }}
                        @else
                        Sale Bill No. : {{ $record->sale_bill_no }}
                        @endif                        
                    </td>
                    <td>{{ $record->user->name }}</td>
                    <td>{{ $record->remarks }}</td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br /><br />
                        @if($record->is_new_party==0)
                        @else
                        <span class="btn btn-info btn-sm css-toggler mb-1"
                            data-sr-css-class-toggle-target="#record-{{ $record->id }}" data-sr-css-class-toggle-class="hidden">
                            Details
                        </span>
                        @endif
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
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->newComplaintItem as $k => $lead_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $lead_item->Item->name ?? Null}}</td>
                                    <td>{{ $lead_item->qty }}</td>
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