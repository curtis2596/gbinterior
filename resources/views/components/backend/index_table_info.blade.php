<span class="badge bg-info">Created : {{ if_date_time($record->created_at)}}</span>
<span class="badge bg-info">By : {{ $userList[$record->created_by] ?? ""}}</span>
<br/>

<span class="badge bg-info">Updated : {{ if_date_time($record->updated_at)}}</span>
<span class="badge bg-info">By : {{ $userList[$record->updated_by] ?? ""}}</span>
