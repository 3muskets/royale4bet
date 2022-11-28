@extends('layouts.app')

@section('head')
<script type="text/javascript">
    $(document).ready(function()
    {
        prepareLocale();
        
        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));
    });

    function prepareLocale() 
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function submitMainForm()
    {
        if($("#mainForm").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm").attr("enabled",0);

        $.ajax({
            url: '/ajax/wallet/transfer',
            type: 'POST',
            data: new FormData($("#mainForm")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success:function(data)
            {
                data = JSON.parse(data);

                if(data.success)
                {
                    alert(locale['success']);
                }
                else
                {
                    alert(data.error);
                }
                
                $("#mainForm").attr("enabled",1);
            }
        });
    }
	
</script>

<style>
    select,input
    {
        box-shadow: none !important;
        outline: none !important;
    }

    select,input::placeholder
    {
        color: #000 !important;
    }

    input::placeholder
    {
        color: #bcbcbc !important;
    }

    label
    {
        color: #000;
    }
    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }

    .page-title
    {
        background:#27273F;
        font-size:16px;
        font-weight: bold;
    }
    
    .bank-option
    {
        display: flex;
        align-items: center;
    }

    .bank-option.selected
    {
        border: 1px solid black;
    }

    .btn-submit
    {
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 5px;
        border:0;
        background: #CF2029;
        padding: 5px;
        width: 100%;
    }
    #notice
    {
        list-style-type: none;
    }
    #notice li
    {
        color: darkgrey;
    }
    ul li::before 
    {
        content: "\2022";
        color: #dd214c;
        font-weight: bold;
        display: inline-block; 
        width: 1em;
        margin-left: -1em;
    }

    @media(max-width: 575.98px)
    {
        #wallet
        {
            justify-content: center;
        }
    }
</style>

@endsection

@section('content')

<div class="w-100 page-title p-2">
    Withdrawal
</div>

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">

                <form id="mainForm">
                    <div class="form-group row" id="wallet" style="align-items: center;">
                        <label class="col-sm-2 col-form-label">Transfer from</label>
                        <div class="col-sm-2">
                            <select class="" name="from" style="width: 100%">
                                <option value="0">Main Wallet</option>
                                <option value="1">Gameplay</option>
                                <option value="2">BBIN</option>
                                <option value="3">IBC</option>
                                <option value="4">Allbet</option>
                                <option value="6">CQ9</option>
                                <option value="7">WM</option>
                                <option value="8">Joker</option>
                                <option value="9">PSB4D</option>
                                <option value="10">Spade Gaming</option>
                                <option value="11">QQ Keno</option>
                                <option value="12">CMD</option>
                                <option value="13">M8BET</option>
                                <option value="14">DIGMAAN</option>
                                <option value="15">Ebet</option>
                                <option value="16">IA</option>
                                <option value="17">NLIVE22</option>
                                <option value="200">MEGA</option>
                            </select>
                        </div>

                        <i class="fa fa-exchange" style="cursor: pointer; color: black;"></i>

                        <div class="col-sm-2">
                            <select class="" name="to" style="width: 100%;">
                                <option value="0">Main Wallet</option>
                                <option value="1">Gameplay</option>
                                <option value="2">BBIN</option>
                                <option value="3">IBC</option>
                                <option value="4">Allbet</option>
                                <option value="6">CQ9</option>
                                <option value="7">WM</option>
                                <option value="8">Joker</option>
                                <option value="9">PSB4D</option>
                                <option value="10">Spade Gaming</option>
                                <option value="11">QQ Keno</option>
                                <option value="12">CMD</option>
                                <option value="13">M8BET</option>
                                <option value="14">DIGMAAN</option>
                                <option value="15">Ebet</option>
                                <option value="16">IA</option>
                                <option value="17">NLIVE22</option>
                                <option value="200">MEGA</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label">Amount</label>
                        <div class="col-sm-2">
                          <input type="number" class="" name="amount">
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label">Promotion</label>
                        <div class="col-sm-2">
                          <select class="" name="promo">
                                <option value="" style="width:13px;">No, thanks</option>
                          </select>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- <div class="card" style="background: transparent;">

    <div class="card-header mb-0">
        <i class="fa fa-money" style="padding: 7px;"></i> <span>TRANSFER</span>
    </div>

    <div class="card-body">

        <div class="card">
            <div class="card-body card-custom-v2">
                <form id="mainForm">
                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label">Transfer from</label>
                        <div class="col-sm-3">
                            <select class="form-control form-control-sm" name="from">
                                <option value="0">Main Wallet</option>
                                <option value="1">Gameplay</option>
                                <option value="2">BBIN</option>
                                <option value="3">IBC</option>
                                <option value="4">Allbet</option>
                                <option value="6">CQ9</option>
                                <option value="7">WM</option>
                                <option value="8">Joker</option>
                                <option value="9">PSB4D</option>
                                <option value="10">Spade Gaming</option>
                                <option value="11">QQ Keno</option>
                                <option value="12">CMD</option>
                                <option value="13">M8BET</option>
                                <option value="14">DIGMAAN</option>
                                <option value="15">Ebet</option>
                                <option value="16">IA</option>
                                <option value="17">NLIVE22</option>
                                <option value="200">MEGA</option>
                            </select>
                        </div>

                        <i class="fa fa-exchange" style="cursor: pointer;"></i>

                        <div class="col-sm-3">
                            <select class="form-control-sm" name="to">
                                <option value="0">Main Wallet</option>
                                <option value="1">Gameplay</option>
                                <option value="2">BBIN</option>
                                <option value="3">IBC</option>
                                <option value="4">Allbet</option>
                                <option value="6">CQ9</option>
                                <option value="7">WM</option>
                                <option value="8">Joker</option>
                                <option value="9">PSB4D</option>
                                <option value="10">Spade Gaming</option>
                                <option value="11">QQ Keno</option>
                                <option value="12">CMD</option>
                                <option value="13">M8BET</option>
                                <option value="14">DIGMAAN</option>
                                <option value="15">Ebet</option>
                                <option value="16">IA</option>
                                <option value="17">NLIVE22</option>
                                <option value="200">MEGA</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label">Amount</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control form-control-sm" name="amount">
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center;">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm-12 offset-2">
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        	<div class="row mt-4"> 
                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>GAMEPLAY</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="1">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>BBIN</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="2">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>IBC</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="3">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>ALLBET</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="4">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>CQ9</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="6">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>WM</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="7">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>JOKER</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="8">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>PSB4D</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="9">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>SPADE GAMING</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="10">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>QQ KENO</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="11">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>CMD</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="12">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>M8BET</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="13">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>DIGMAAN</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="14">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>EBET</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="15">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>IA</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="16">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>NLIVE22</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="17">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 px-1 py-2">
                    <div class="card">
                        <div class="card-body card-custom-v2">
                            <form>
                                <label>MEGA</label>
                                <input type="hidden" value=1">
                                <input type="number" class="amount">
                                <div class="d-flex w-100" style="justify-content: end;">
                                    <button type="submit" class="btn btn-submit" onclick="submitMainForm(this)" data-value="200">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div> -->
@endsection