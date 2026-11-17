@extends('layouts.auth')
@section('content')
	<?php
	$setting = \App\Models\Setting::pluck('value','name')->toArray();
	$auth_logo = isset($setting['auth_logo']) ? 'uploads/'.$setting['auth_logo'] : 'assets/media/logos/logo-light.png';
	$auth_page_heading = isset($setting['auth_page_heading']) ? $setting['auth_page_heading'] : 'wwww.webexert.com';
	$site_title = isset($setting['site_title']) ? $setting['site_title'] : 'wwww.webexert.com';
	$auth_image = isset($setting['auth_image']) ? 'uploads/'.$setting['auth_image'] : 'assets/media/svg/illustrations/login-visual-1.svg';
	$copy_right = isset($setting['copy_right']) ? $setting['copy_right'] : 'wwww.webexert.com';
	$phone = isset($setting['phone']) ? $setting['phone'] : 'wwww.webexert.com';
	$email = isset($setting['email']) ? $setting['email'] : 'wwww.webexert.com';
	?>
	<style>
	        .photo:hover {
          
            transform: scale(1.02);
        }
		.photo{
			border-radius: 15px;
			            background:#c6ac60;
            padding: 6px;
            box-shadow: 
                0 20px 40px rgba(255,215,0,0.3),
                0 0 80px rgba(255,107,53,0.2),
                inset 0 0 20px rgba(255,255,255,0.1);
            animation: glow 3s ease-in-out infinite alternate;
			z-index: 999;
		}
		        .photo-frame {
            position: relative;
            border-radius: 20px;
            overflow: hidden;

        }

        @keyframes glow {
            0% { 
                box-shadow: 
                    0 20px 40px rgba(255,215,0,0.3),
                    0 0 80px rgba(255,107,53,0.2),
                    inset 0 0 20px rgba(255,255,255,0.1);
            }
            100% { 
                box-shadow: 
                    0 25px 50px rgba(255,215,0,0.5),
                    0 0 100px rgba(255,107,53,0.4),
                    inset 0 0 30px rgba(255,255,255,0.2);
            }
        }
		 .logobody-side {
            position: relative;
        }
		

        /* Animated background particles */
        .logobody-side::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 20px 30px, #fff, transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(255,215,0,0.3), transparent),
                radial-gradient(1px 1px at 90px 40px, #fff, transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(255,215,0,0.3), transparent);
            background-repeat: repeat;
            background-size: 150px 100px;
            animation: sparkle 20s linear infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes sparkle {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }
	</style>
	<div class="d-flex flex-column flex-root">
		<!--begin::Login-->
		<div class="login login-1 login-signin-on d-flex flex-column flex-lg-row flex-column-fluid bg-white" id="kt_login">
			<!--begin::Aside-->
			<div class="logobody-side login-aside d-flex flex-column flex-row-auto" style="background-color: #000000;">
				<!--begin::Aside Top-->
				<div class="d-flex flex-column-auto flex-column pt-lg-3 pt-5">
					<!--begin::Aside header-->
					<a href="#" class="text-center py-5">
						<img src="{{ asset($auth_logo) }}" class="max-h-120px" alt="" />
					</a>
					<!--end::Aside header-->
					<!--begin::Aside title-->
					<!--<h3 class="font-weight-bolder text-center font-size-h4 font-size-h1-lg" style="color: #986923;"><?php echo stripcslashes($auth_page_heading )?></h3>-->
					<!--end::Aside title-->
				</div>
				<!--end::Aside Top-->
				<!--begin::Aside Bottom-->
				<!--<div class="aside-img d-flex flex-row-fluid bgi-no-repeat bgi-position-y-bottom bgi-position-x-center"
						style="background-image: url('{{ asset('assets/gymland.jpeg') }}'); background-size: cover;">
				</div>-->
				<div class="photo-frame aside-img d-flex flex-row-fluid justify-content-center align-items-end" style="overflow:hidden;">
					<img class="photo" src="{{ asset($auth_image) }}" alt="GymLand Image" style="max-width: 100%; max-height:530px; height: 100% ; object-fit: cover; ;
            transition: all 0.3s ease;">
				</div>

				<!--end::Aside Bottom-->
			</div>
			<!--begin::Aside-->
			<!--begin::Content-->
			<div class="login-content flex-row-fluid d-flex flex-column justify-content-center position-relative overflow-hidden p-7 mx-auto">
				<!--begin::Content body-->
				<div class="d-flex flex-column-fluid flex-center">
					<!--begin::Signin-->
					<div class="login-form login-signin">
						<!--begin::Form-->
						<form class="form" action="{{ route('login') }}" method="post" novalidate="novalidate" >

							@csrf

							<!--begin::Title-->
							<div class="pb-13 pt-lg-0 pt-5">
								<h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">
									Welcome to {{$site_title}}</h3>
								{{--<span class="text-muted font-weight-bold font-size-h4">New Here?
									<a href="javascript:;" id="kt_login_signup" class="text-primary font-weight-bolder">Create an Account</a></span>--}}
								@if (Session::has('error'))
									<h5 style="color: red"><strong>{{ Session::get('error') }}</strong></h5>
								@endif
								@if (Session::has('subscription_expired'))
									<div id="subscriptionExpiredAlert" class="alert alert-danger">
										<h5><strong>Subscription Expired!</strong></h5>
										<p>Your subscription has expired. Please contact the administrator to renew your subscription.</p>
										<p><strong>Contact Information:</strong></p>
										<p>Phone: {{ $phone }}</p>
										<p>Email: {{ $email }}</p>
									</div>
								@endif
							</div>
							<!--begin::Title-->
							<!--begin::Form group-->
							<div class="form-group">

								<label class="font-size-h6 font-weight-bolder text-dark">Email</label>
								<input class="@error('email') is-invalid @enderror form-control form-control-solid h-auto py-6 px-6 rounded-lg" type="email" name="email" value="{{ old('email') }}" autocomplete="off" />
								@error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group">
								<div class="d-flex justify-content-between mt-n5">
									<label class="font-size-h6 font-weight-bolder text-dark pt-5">Password</label>
									<a href="{{ route('password.request') }}" class="text-primary font-size-h6 font-weight-bolder text-hover-primary pt-5" >Forgot Password ?</a>
								</div>
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg @error('password') is-invalid @enderror"  required autocomplete="current-password" type="password" name="password"/>
									@error('password')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong> </span>@enderror
							</div>
							<!--end::Form group-->
							<!--begin::Action-->
							<div class="pb-lg-0 pb-5">
								<button type="submit"  class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3">Sign In</button>
								{{--<button type="button" class="btn btn-light-primary font-weight-bolder px-8 py-4 my-3 font-size-lg">
									<span class="svg-icon svg-icon-md">
										<!--begin::Svg Icon | path:assets/media/svg/social-icons/google.svg-->
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
											<path d="M19.9895 10.1871C19.9895 9.36767 19.9214 8.76973 19.7742 8.14966H10.1992V11.848H15.8195C15.7062 12.7671 15.0943 14.1512 13.7346 15.0813L13.7155 15.2051L16.7429 17.4969L16.9527 17.5174C18.879 15.7789 19.9895 13.221 19.9895 10.1871Z" fill="#4285F4" />
											<path d="M10.1993 19.9313C12.9527 19.9313 15.2643 19.0454 16.9527 17.5174L13.7346 15.0813C12.8734 15.6682 11.7176 16.0779 10.1993 16.0779C7.50243 16.0779 5.21352 14.3395 4.39759 11.9366L4.27799 11.9466L1.13003 14.3273L1.08887 14.4391C2.76588 17.6945 6.21061 19.9313 10.1993 19.9313Z" fill="#34A853" />
											<path d="M4.39748 11.9366C4.18219 11.3166 4.05759 10.6521 4.05759 9.96565C4.05759 9.27909 4.18219 8.61473 4.38615 7.99466L4.38045 7.8626L1.19304 5.44366L1.08875 5.49214C0.397576 6.84305 0.000976562 8.36008 0.000976562 9.96565C0.000976562 11.5712 0.397576 13.0882 1.08875 14.4391L4.39748 11.9366Z" fill="#FBBC05" />
											<path d="M10.1993 3.85336C12.1142 3.85336 13.406 4.66168 14.1425 5.33717L17.0207 2.59107C15.253 0.985496 12.9527 0 10.1993 0C6.2106 0 2.76588 2.23672 1.08887 5.49214L4.38626 7.99466C5.21352 5.59183 7.50242 3.85336 10.1993 3.85336Z" fill="#EB4335" />
										</svg>
										<!--end::Svg Icon-->
									</span>Sign in with Google</button>--}}
							</div>
							<!--end::Action-->
						</form>
						<!--end::Form-->
					</div>
					<!--end::Signin-->
					<!--begin::Signup-->
					{{--<div class="login-form login-signup">
						<!--begin::Form-->
						<form class="form" novalidate="novalidate" id="kt_login_signup_form">
							<!--begin::Title-->
							<div class="pb-13 pt-lg-0 pt-5">
								<h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Sign Up</h3>
								<p class="text-muted font-weight-bold font-size-h4">Enter your details to create your account</p>
							</div>
							<!--end::Title-->
							<!--begin::Form group-->
							<div class="form-group">
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg font-size-h6" type="text" placeholder="Fullname" name="fullname" autocomplete="off" />
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group">
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg font-size-h6" type="email" placeholder="Email" name="email" autocomplete="off" />
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group">
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg font-size-h6" type="password" placeholder="Password" name="password" autocomplete="off" />
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group">
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg font-size-h6" type="password" placeholder="Confirm password" name="cpassword" autocomplete="off" />
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group">
								<label class="checkbox mb-0">
									<input type="checkbox" name="agree" />
									<span></span>
									<div class="ml-2">I Agree the
										<a href="#">terms and conditions</a>.</div>
								</label>
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group d-flex flex-wrap pb-lg-0 pb-3">
								<button type="button" id="kt_login_signup_submit" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-4">Submit</button>
								<button type="button" id="kt_login_signup_cancel" class="btn btn-light-primary font-weight-bolder font-size-h6 px-8 py-4 my-3">Cancel</button>
							</div>
							<!--end::Form group-->
						</form>
						<!--end::Form-->
					</div>--}}
					<!--end::Signup-->
					<!--begin::Forgot-->
					{{--<div class="login-form login-forgot">
						<!--begin::Form-->
						<form class="form" novalidate="novalidate" id="kt_login_forgot_form">
							<!--begin::Title-->
							<div class="pb-13 pt-lg-0 pt-5">
								<h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Forgotten Password ?</h3>
								<p class="text-muted font-weight-bold font-size-h4">Enter your email to reset your password</p>
							</div>
							<!--end::Title-->
							<!--begin::Form group-->
							<div class="form-group">
								<input class="form-control form-control-solid h-auto py-6 px-6 rounded-lg font-size-h6" type="email" placeholder="Email" name="email" autocomplete="off" />
							</div>
							<!--end::Form group-->
							<!--begin::Form group-->
							<div class="form-group d-flex flex-wrap pb-lg-0">
								<button type="button" id="kt_login_forgot_submit" class="btn btn-primary font-weight-bolder font-size-h6 px-8 py-4 my-3 mr-4">Submit</button>
								<button type="button" id="kt_login_forgot_cancel" class="btn btn-light-primary font-weight-bolder font-size-h6 px-8 py-4 my-3">Cancel</button>
							</div>
							<!--end::Form group-->
						</form>
						<!--end::Form-->
					</div>--}}
					<!--end::Forgot-->
				</div>
				<!--end::Content body-->
				<!--begin::Content footer-->
				<div class="d-flex justify-content-lg-start justify-content-center align-items-end py-7 py-lg-0">
					<div class="text-dark-50 font-size-lg font-weight-bolder mr-10">
						<span class="mr-1">{{ date('Y') }}©</span>
						<a href="#" target="_blank" class="text-dark-75 text-hover-primary">{{ $copy_right }}</a>
					</div>
{{--					<a href="#/reviews/" target="_blank" class="text-primary font-weight-bolder font-size-lg">Reviews</a>--}}
{{--					<a href="#/about-us/" target="_blank" class="text-primary ml-5 font-weight-bolder font-size-lg">About</a>--}}
{{--					<a href="#/contact-us/" target="_blank" class="text-primary ml-5 font-weight-bolder font-size-lg">Contact</a>--}}
				</div>
				<!--end::Content footer-->
			</div>
			<!--end::Content-->
		</div>
		<!--end::Login-->
	</div>

	<!-- Subscription Expired Modal -->
	@if (Session::has('subscription_expired'))
	<div class="modal fade" id="subscriptionExpiredModal" tabindex="-1" role="dialog" aria-labelledby="subscriptionExpiredModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header bg-danger text-white">
					<h5 class="modal-title" id="subscriptionExpiredModalLabel">
						<i class="fas fa-exclamation-triangle"></i> Subscription Expired
					</h5>
					<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="text-center mb-4">
						<i class="fas fa-calendar-times fa-3x text-danger mb-3"></i>
						<h4 class="text-danger">Your subscription has expired!</h4>
					</div>
					<p class="text-center">Your subscription period has ended. Please contact the administrator to renew your subscription and regain access to the system.</p>
					
					<div class="alert alert-info">
						<h6><strong>Contact Information:</strong></h6>
						<div class="row">
							<div class="col-md-6">
								<p><i class="fas fa-phone"></i> <strong>Phone:</strong><br>
								{{ $phone }}</p>
							</div>
							<div class="col-md-6">
								<p><i class="fas fa-envelope"></i> <strong>Email:</strong><br>
								{{ $email }}</p>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	@endif
@endsection
@section('scripts')
@if (Session::has('subscription_expired'))
<script>
// Multiple approaches to ensure modal shows
function showSubscriptionModal() {
    console.log('Attempting to show subscription modal...');
    
    // Try jQuery modal first (most reliable for this theme)
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        console.log('Using jQuery modal...');
        $('#subscriptionExpiredModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        return;
    }
    
    // Try Bootstrap 5 modal
    if (typeof bootstrap !== 'undefined') {
        console.log('Using Bootstrap 5 modal...');
        var modalElement = document.getElementById('subscriptionExpiredModal');
        if (modalElement) {
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
            return;
        }
    }
    
    // Direct show as last resort
    console.log('Using direct show...');
    var modalElement = document.getElementById('subscriptionExpiredModal');
    if (modalElement) {
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Add backdrop
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
}

// Try multiple event listeners
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(showSubscriptionModal, 100);
});

window.addEventListener('load', function() {
    setTimeout(showSubscriptionModal, 200);
});

// Also try immediately
setTimeout(showSubscriptionModal, 300);
</script>
@endif
@endsection
