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
        width: 100%;
/*        border: 2px solid #77a5eb !important;*/
        background: black !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        outline: none !important;
        color: white !important;
        padding: 5px 10px !important;

    }

    select,input::placeholder
    {
        color: #bcbcbc !important;
    }

    input::placeholder
    {
        color: #bcbcbc !important;
        text-align: center;
    }
	.card-custom-v2
    {
        background: #23214a;
        border: 2px solid #9ee2fe;
        border-radius: 20px;
        box-shadow: inset 0 0 15px #77a5eb;
    }
    .card
    {
        border: none;
        background: transparent;
    }
    .card-header
    {
        background: linear-gradient(180deg,#131228,#140133);
    }
    .card-header span
    {
        -webkit-mask: linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        animation: shimmer 2.5s infinite;
        font-weight: bold;
    }
    .btn-submit
    {
        width: 30%;
        height: 40px;
        color: #ffffff;
        margin: 5px 2px;
        padding: 0;
        border-radius: 8px;
        border:0;
        background: linear-gradient(180deg,rgba(43,67,129,.96),rgba(31,10,90,.96));

    }
    .col-form-label
    {
        text-align: right;
    }
</style>

@endsection

@section('content')

<div class="card" style="background: transparent;">

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

        	<!-- <div class="row mt-4"> 
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

            </div> -->
        </div>
    </div>

</div>
@endsection