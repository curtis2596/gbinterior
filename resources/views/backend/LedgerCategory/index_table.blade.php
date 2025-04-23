<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Type</th>
                    <th><?= sortable_anchor('name', 'Name') ?></th>                    
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $typeList[$record->type] ?? "" }}</td>
                    <td>{{ $record->name }}</td>                    
                    <td>
                        @if(!$record->is_pre_defined)
                            <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                        @endif
                    </td>
                    <td>
                        @if($record->is_pre_defined)
                            <span class="badge bg-success">Pre-Defined</span>
                            <span class="badge bg-info">Pre-Defined Records can not edit or Delete</span>
                        @else
                            <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
                        @endif
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