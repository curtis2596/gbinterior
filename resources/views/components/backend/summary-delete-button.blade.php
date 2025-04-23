<form action="{{ $url }}" method="POST" {{ $attributes->merge(['class' => 'summary-delete-form']) }}>
    {{ csrf_field() }}
    {{ method_field('DELETE') }}
    <button class="btn btn-outline-danger summary-action-button">
        <i class="icon-trash label-icon"></i>
    </button>
</form>