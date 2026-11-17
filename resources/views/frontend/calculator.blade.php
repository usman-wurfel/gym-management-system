<!doctype html>
<?php
$setting = \App\Models\Setting::pluck('value','name')->toArray();

$favicon = isset($setting['favicon']) ? 'uploads/'.$setting['favicon'] : 'assets/media/logos/favicon.ico';
$site_title = isset($setting['site_title']) ? $setting['site_title'] : 'wwww.webexert.com';

?>
<html lang="en">
<head>
    <title>Calculator</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{asset("calculator-assets/css/style.css")}}">
    <link rel="shortcut icon" href="{{ asset($favicon) }}" />
</head>
<body>
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-5">
                <h2 class="heading-section">Calculator</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="wrapper">
                    <div class="row no-gutters">
                        <div class="col-md-7 d-flex align-items-stretch">
                            <div class="contact-wrap w-100 p-md-5 p-4">
                                <h3 class="mb-4">Change Values</h3>
                                <div id="form-message-warning" class="mb-4"></div>
                                <div id="form-message-success" class="mb-4">
                                    Your message was sent, thank you!
                                </div>
                                <form method="POST" id="contactForm" name="contactForm">
                                    <div class="form-group row {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label class="col-3">Basic Price</label>
                                        <div class="col-9">
                                            {{ Form::number('basic', 0, ['class' => 'form-control changeEvent ','id'=>'basic','placeholder'=>'Enter Here','required'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('basic') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('lp_vat') ? 'has-error' : '' }}">
                                        <label class="col-3">LP VAT</label>
                                        <div class="col-9">
                                            {{ Form::number('lp_vat', 0, ['class' => 'form-control form-control-solid changeEvent ','id'=>'lp_vat','placeholder'=>'Enter Here','required'=>'true','disabled'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('lp_vat') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('deposit') ? 'has-error' : '' }}">
                                        <label class="col-3">Deposit</label>
                                        <div class="col-9">
                                            {{ Form::number('deposit', 0, ['class' => 'form-control changeEvent ','id'=>'deposit','placeholder'=>'Enter Here','required'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('deposit') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('term') ? 'has-error' : '' }}">
                                        <label class="col-3">Term</label>
                                        <div class="col-9">
                                            {{ Form::number('term', 0, ['class' => 'form-control changeEvent ','id'=>'term','placeholder'=>'Enter Here','required'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('term') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('balloon') ? 'has-error' : '' }}">
                                        <label class="col-3">Balloon</label>
                                        <div class="col-9">
                                            {{ Form::number('balloon', 0, ['class' => 'form-control changeEvent ','id'=>'balloon','placeholder'=>'Enter Here','required'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('balloon') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('t_comm') ? 'has-error' : '' }}">
                                        <label class="col-3">T Comm</label>
                                        <div class="col-9">
                                            {{ Form::number('t_comm', 0, ['class' => 'form-control changeEvent ','id'=>'t_comm','placeholder'=>'Enter Here','required'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('t_comm') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row {{ $errors->has('payment') ? 'has-error' : '' }}">
                                        <label class="col-3">Payment</label>
                                        <div class="col-9">
                                            {{ Form::number('payment', 0, ['class' => 'form-control changeEvent form-control-solid','id'=>'payment','placeholder'=>'Enter Here','required'=>'true','disabled'=>'true']) }}
                                            <span class="text-danger">{{ $errors->first('payment') }}</span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex align-items-stretch">
                            <div class="info-wrap bg-primary w-100 p-lg-5 p-4">
                                <h3 class="mb-4 mt-md-4">Calculated Value</h3>
                                <div class="dbox w-100 d-flex align-items-start">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-calculator"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <p><span>Interest:</span>
                                            {{ Form::number('interest', 0, ['class' => 'form-control  form-control-solid','id'=>'interest','placeholder'=>'Enter Here','readonly'=>'true','disabled'=>'true']) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="dbox w-100 d-flex align-items-center">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-calculator"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <p><span>TAP-B+C:</span>
                                            {{ Form::number('tap_b', 0, ['class' => 'form-control  form-control-solid','id'=>'tap_b','placeholder'=>'Enter Here','readonly'=>'true','disabled'=>'true']) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="dbox w-100 d-flex align-items-center">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-calculator"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <p><span>BP- D :</span>
                                            {{ Form::number('bp_d', 0, ['class' => 'form-control  form-control-solid','id'=>'bp_d','placeholder'=>'Enter Here','readonly'=>'true','disabled'=>'true']) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="dbox w-100 d-flex align-items-center">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-calculator"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <p><span>Rate B</span>
                                            {{ Form::number('rate_b', 0, ['class' => 'form-control  form-control-solid','id'=>'rate_b','placeholder'=>'Enter Here','readonly'=>'true','disabled'=>'true']) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="dbox w-100 d-flex align-items-center">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fa fa-edit"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <p><span>Rate</span>
                                            {{ Form::number('rate', 0, ['class' => 'form-control changeEvent ','id'=>'rate','placeholder'=>'Enter Here',]) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{asset("calculator-assets/js/jquery.min.js")}}"></script>
<script src="{{asset("calculator-assets/js/popper.js")}}"></script>
<script src="{{asset("calculator-assets/js/bootstrap.min.js")}}"></script>
<script src="{{asset("calculator-assets/js/jquery.validate.min.js")}}"></script>
<script src="{{asset("calculator-assets/js/main.js")}}"></script>
<script !src="">
    $(".changeEvent").change(function() {
        cal();
    });
    function cal() {
        let basic = parseInt($("#basic").val());
        let deposit = parseInt($("#deposit").val());
        let term = parseInt($("#term").val());
        let balloon = parseInt($("#balloon").val());
        let t_comm = parseInt($("#t_comm").val());

        let rate_b = $('#rate').val() / 10000;
        rate_b_a = Number(rate_b).toFixed(2);
        $('#rate_b').val(rate_b);

        let lp_vat = $('#basic').val() / 5;
        lp_vat_a = lp_vat.toFixed(2);
        $('#lp_vat').val(lp_vat_a);

        let bp_d = basic - deposit;
        bp_d_a = bp_d.toFixed(2);
        $('#bp_d').val(bp_d_a);

        let interest = bp_d * rate_b / 12 * term;
        interest_a = interest.toFixed(2);
        $('#interest').val(interest_a);

        let tap_b = interest + bp_d - balloon + t_comm;
        tap_b_a = tap_b.toFixed(2);
        $('#tap_b').val(tap_b_a);

        let payment = tap_b / term;
        payment_a = payment.toFixed(2);
        $('#payment').val(payment_a);

    }
</script>

</body>
</html>

