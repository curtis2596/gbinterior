<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Customer Info</th>
                    <th>Level</th>
                    <th>Satus</th>
                    <th>Comments</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>
                        @if($record->is_new == 0)
                        Party : {{ $record->party->name }}
                        <br />
                        @else
                        Name : {{ $record->customer_name }}
                        <br />
                        Email : {{ $record->customer_email }}
                        <br />
                        @endif
                    </td>
                    <td>
                        {{ $record->level }}
                    </td>
                    <td>
                        @if($record->status == 'Pending')
                        Nothing to show
                        @elseif($record->status == 'follow_up')
                        Follow Up By: {{ $record->user->name ?? 'N/A' }}
                        <br />
                        Follow Up Date: {{ $record->follow_up_date ?? 'N/A' }}
                        <br />
                        Follow Up Type: {{ $record->follow_up_type ?? 'N/A' }}
                        @elseif($record->status == 'Not Interested')
                        Not Interested Reason: {{ $record->not_in_interested_reason ?? 'N/A' }}
                        @elseif($record->status == 'Mature')
                        Action To Take: {{ $record->mature_action_type ?? 'N/A' }}
                        @else
                        {{ $record->status }}
                        @endif
                    </td>
                    <td>
                        {{ $record->comments }}
                    </td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />

                        <br /><br />

                        @if($record->is_include_items==0)
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
                                @foreach($record->leadItem as $k => $lead_item)
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