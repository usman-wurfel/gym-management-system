@php
    if(auth()->user()->image){
        $logo = asset('images/'.auth()->user()->image);
    }elseif(auth()->user()->is_admin == 1 and auth()->user()->user_id != null){
        if(auth()->user()->parent->image){
            $logo = asset('images/'.auth()->user()->parent->image);
        }
    }
@endphp
<!--begin::Page loader-->
		<div class="page-loader page-loader-logo">
			<img alt="Logo" class="max-h-75px" src="{{ asset($logo) }}" />
			<div class="spinner spinner-primary"></div>
		</div>

		<!--end::Page Loader-->
