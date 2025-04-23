@extends($layout)

@section('content')

<div class="login-main">
    <form class="theme-form" method="POST" action="{{ route('login') }}">
        @csrf
        <h2 class="text-center">Sign in to account</h2>
        <p class="text-center">Enter your email &amp; password to login</p>

        <x-Backend.session-flash />

        <x-Inputs.text-field type="email" name="email" label="Email" placeholder="Enter Email" />
        <div class="form-group">
            <label class="col-form-label">Password</label>
            <div class="form-input position-relative">
                <input class="form-control" type="password" name="password" required="" placeholder="*********">
                <div class="show-hide"><span class="show"></span></div>
            </div>
        </div>
        <div class="form-group mb-0 checkbox-checked">
            <a class="link" href="forget-password.html">Forgot password?</a>
            <div class="text-end mt-3">
                <button class="btn btn-primary btn-block w-100" type="submit">Sign in </button>
            </div>
        </div>

    </form>
</div>
@endsection
