<!-- @extends('layouts.auth.app')

@section('content')
<div class="container-fluid p-0">
    <div class="row no-gutters">
        <div class="col-lg-12">
            <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                <div class="w-100">
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <div>
                                <div class="text-center">
                                    <div>
                                        <a href="#" class="logo">
                                            <img style="width: 250px;" src="{{ asset('assets/images/logo.png') }}" alt="logo">
                                        </a>
                                    </div>
                                    <h4 class="font-size-18 mt-4">Welcome Back !</h4>
                                    <p class="text-muted">Sign in to start your session.</p>
                                </div>

                                <div class="p-2 mt-5">
                                    <form method="POST" action="{{ route('login.attempt') }}" class="form-horizontal" id="loginForm" autocomplete="off">
                                        @csrf

                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                {{ $errors->first() }}
                                            </div>
                                        @endif

                                        <div class="form-group auth-form-group-custom mb-4">
                                            <i class="ri-user-2-line auti-custom-input-icon"></i>
                                            <label for="usr">Username</label>
                                            <input type="text" id="usr" name="usr"
                                                class="form-control @error('usr') is-invalid @enderror"
                                                value="{{ old('usr') }}" placeholder="Enter username"
                                                autofocus autocomplete="username">
                                            @error('usr')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group auth-form-group-custom mb-2">
                                            <i class="ri-lock-2-line auti-custom-input-icon"></i>
                                            <label for="password">Password</label>
                                            <input type="password" id="password" name="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                placeholder="Enter password" autocomplete="current-password">
                                            @error('password')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group d-flex align-items-center justify-content-between mb-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="remember"
                                                    name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="remember">Remember me</label>
                                            </div>
                                        </div>

                                        <div class="mt-4 text-center">
                                            <button class="btn btn-primary w-md waves-effect waves-light" type="submit" id="loginBtn">
                                                <span class="default-text">Log In</span>
                                                <span class="loading-text" style="display:none;">Processing...</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div>
    </div>
</div>
@endsection

<script>
document.getElementById('loginForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    if (!btn) return;
    btn.disabled = true;
    btn.querySelector('.default-text').style.display = 'none';
    btn.querySelector('.loading-text').style.display = 'inline';
});
</script> -->
