@extends($layout)

@section('content')

@include($partial_path . ".page_header")


<div class="login-card login-dark">
    <div class="login-main">
        <form class="theme-form mb-1" action="{{ route('user.change.password') }}" method="post">
            @csrf
            <h2 class="mb-3">Change Your Password</h2>
            <x-Backend.form-errors />
            <x-Backend.session-flash />

            <x-Inputs.text-field type="password" name="old_password" label="Old Password" placeholder="*********" required="true" />
            <x-Inputs.text-field type="password" name="new_password" label="New Password" placeholder="*********" required="true" />
            <x-Inputs.text-field type="password" name="confirm_password" label="Confirm Password" placeholder="*********" required="true" />

            <div class="form-group mb-0 checkbox-checked">
                <button class="btn btn-primary btn-block w-100 mt-3" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>


@endsection