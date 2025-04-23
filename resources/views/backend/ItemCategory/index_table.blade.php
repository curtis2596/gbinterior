<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">

        <div class="d-flex flex-wrap gap-2 action-buttons">
            {{--<a class="btn btn-info waves-effect waves-light" href="{{ url(route($routePrefix . '.csv', $search)) }}">Export CSV</a>--}}
        </div>

        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Parent</th>
                    <th><?= sortable_anchor('name', 'Name') ?></th>
                    <th><?= sortable_anchor('short_name', 'Short Name') ?></th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->parent?->name }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->short_name }}</td>
                    <td>
                        @if(!$record->is_pre_defined)
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                        @endif
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
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