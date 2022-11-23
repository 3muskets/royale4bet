@extends('my_profile')

@section('head')
@endsection

@section('details')
<div class="body">
    <div class="account-info section" data-section="profile">
        <div class="title">
            <span></span>
        </div>
        <div class="profile-container">
            <div class="section-body">
                <div id="content-top-bar" class="tab-container clearfix">
                </div>
                <div data-tab="deposit" class="deposit-container section-tab tab-focus">
                    <div class="tab-inner-container clearfix">
                        <div data-url="/my_profile/deposit/new" class="tab-inner-item section-inner-tab selected">
                            <span>{{__('app.dw.new.d_methods') }}</span>
                        </div>
                        <div data-url="/my_profile/deposit/new?status" class="tab-inner-item section-inner-tab">
                            <span>{{__('app.dw.new.d_status') }}</span>
                        </div>
                    </div>
                    <script>
                        $('.tab-inner-container > div').on('click', function () {
                            location.href = $(this).attr('data-url');
                        })
                    </script>
                    <div class="deposit-container-item">
                        <div class="tab-inner-button-container">
                            <div data-url="/my_profile/deposit/new" class="tab-inner-button-item ">
                                <span>{{__('app.dw.new.paymenttype.cash') }}</span>
                            </div>
                            <div data-url="/my_profile/deposit/new?crypto" class="tab-inner-button-item ">
                                <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
                            </div>
                            <div data-url="/my_profile/deposit/new?card" class="tab-inner-button-item selected">
                                <span>{{__('app.dw.new.paymenttype.card') }}</span>
                            </div>
                        </div>
                        <hr style="background: white;margin: 20px 10px;">
                        <style>
                            .card-list {
                                text-align: center;
                            }

                            .card-list img {
                                width: 100px;
                                height: 60px;
                            }
                        </style>
                        <script>
                            (function () {
                                $('.tab-inner-button-container > .tab-inner-button-item').on('click',
                                    function () {
                                        location.href = $(this).attr('data-url');
                                    })
                            })()
                        </script>
                        <!-- <div class="success-msg" style="display: none;">
                            <p>Dear Player.</p>
                            <br>
                            <p>Your account Top-up request has been accepted! Contact your agent to complete
                                the
                                process.</p>
                            <br>
                            <p>Check“ DEPOSIT STATUS” in your wallet to follow up.</p>
                            <br>
                            <p>E-mail us to: 123123@abc.com for any other quires.</p>
                        </div> -->
                        <form method="POST" id="mainForm" style="max-width: 400px;">
                            <div class="card-list">
                                <img src="/images/payment/visa.png" alt="">
                                <img src="/images/payment/mastercard.png" alt="">
                            </div>
                            <div class="form-container">
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{ __('app.dw.new.card_no') }} *</label>
                                        <div class="input-wrapper"> 
                                        	<input name="card_no" type="text">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{ __('app.dw.new.expiry') }} *</label>
                                        <div class="input-wrapper"> 
                                        	<input name="expiry_date" type="text">
                                        </div>
                                    </div>
                                    <div class="form-element">
                                        <label>{{ __('app.dw.new.cvv') }} *</label>
                                        <div class="input-wrapper"> 
                                        	<input name="cvv" type="text">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-btn">
                                <button class="btn btn1" type="submit">{{ __('app.dw.new.button.request') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection