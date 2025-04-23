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
                    <th>Pattern</th>
                    <th>Counter</th>
                    <th>Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

                use App\Models\AutoIncreament;

                $types = AutoIncreament::TYPE_LIST;
                ?>

                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $types[$record->type] ?? ""; }}</td>
                    <td>{{ $record->pattern }}</td>
                    <td>{{ $record->counter }}</td>
                    <td>
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <a class="btn btn-outline-primary summary-action-button" href="{{ route($routePrefix . '.edit',[$record->id]) }}">
                            <i class="icon-pencil-alt"></i>
                        </a>
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