@if(isset($errors) && $errors->any())

<section class="alert alert-danger alert-dismissible fade show">
    <ul class="list-unstyled mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button class="btn btn-close" data-bs-dismiss="alert"></button>
</section>

@endif