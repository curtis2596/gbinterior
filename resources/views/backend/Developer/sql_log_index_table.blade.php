<div class="card" id="page-summary">
    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body p-1">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr class="border-bottom-primary">
                        <th>ID</th>
                        <th>Route Name / Url</th>
                        <th>Actions</th>
                    </tr>

                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->route_name_or_url }}</td>
                        <td>
                            <a download="sql_{{ $record->id }}.txt" href="/{{ $record->sql_log_file }}" class="btn btn-outline-secondary">
                                <i class="fas fa-download"></i>
                                SQL Log
                            </a>
                            @if($record->sql_dml_log_file)
                            <a download="sql_dml_{{ $record->id }}.txt" href="/{{ $record->sql_dml_log_file }}" class="btn btn-outline-secondary">
                                <i class="fas fa-download"></i>
                                DML SQL Log
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <x-Backend.pagination-links :records="$records" />
    </div>
</div>