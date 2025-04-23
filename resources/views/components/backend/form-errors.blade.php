@if ($errors->any())

<div class="card-wrapper rounded-3 h-100 bg-light-secondary">
    <h6 class="sub-title f-w-600">Errors</h6>
    <ol class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ol>
</div>

@endif