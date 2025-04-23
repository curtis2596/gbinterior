@extends($layout)

@section('content')

<div class="auth-full-page-content d-flex p-sm-5 p-4">
    <div class="w-100">
        <div class="d-flex flex-column h-100">
            <div class="mb-4 mb-md-5 text-center">
                <a href="index.html" class="d-block auth-logo">
                    <img src="/assets/images/logo-sm.svg" alt="" height="28"> <span class="logo-txt">Minia</span>
                </a>
            </div>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="auth-content my-auto">
                    <div class="text-center">
                        <h5 class="mb-0">Welcome Back !</h5>
                        <p class="text-muted mt-2">Sign in to continue to Minia.</p>
                    </div>
                    <form class="mt-4 pt-2" action="index.html">
                        <div class="mb-3">
                            <x-Inputs.text-field type="text" name="name" label="Name" placeholder="Enter Name" />
                        </div>
                        <div class="mb-3">
                            <x-Inputs.text-field type="email" name="email" label="Email" placeholder="Enter Email" />
                        </div>
                        <div class="mb-3">
                            <x-Inputs.text-field type="password" name="password" label="Password" placeholder="Enter Password" />
                        </div>
                        <div class="mb-3">
                            <x-Inputs.text-field type="password" name="password_confirmation" label="Confirm Password" placeholder="Enter Confirm Password" />
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log In</button>
                        </div>
                    </form>

                    <div class="mt-4 pt-2 text-center">
                        <div class="signin-other-title">
                            <h5 class="font-size-14 mb-3 text-muted fw-medium">- Sign in with -</h5>
                        </div>

                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <a href="javascript:void()" class="social-list-item bg-primary text-white border-primary">
                                    <i class="mdi mdi-facebook"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="javascript:void()" class="social-list-item bg-info text-white border-info">
                                    <i class="mdi mdi-twitter"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="javascript:void()" class="social-list-item bg-danger text-white border-danger">
                                    <i class="mdi mdi-google"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 text-center">
                        <p class="text-muted mb-0"><a href="/login" class="text-primary fw-semibold"> Login </a> </p>
                    </div>
                </div>
            </form>
            <div class="mt-4 mt-md-5 text-center">
                <p class="mb-0">© <script>
                        document.write(new Date().getFullYear())
                    </script> Minia . Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
            </div>
        </div>
    </div>
</div>

@endsection