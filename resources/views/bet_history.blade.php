@extends('layouts.app')

@section('head')
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();

		(function () {

            var nScrollHight = 0;
            var nScrollTop = 0;
            var nDivHight = $("#main-table").height();
            $("#main-table").scroll(function () {
                nScrollHight = $(this)[0].scrollHeight;
                nScrollTop = $(this)[0].scrollTop;
                var paddingBottom = parseInt($(this).css('padding-bottom')),
                    paddingTop = parseInt($(this).css('padding-top'));
                if (nScrollTop + paddingBottom + paddingTop + nDivHight >= nScrollHight) {
                    load();
                }
            });
        })();

		utils.createSpinner("main-spinner");

		getMainData();

        $("#btn-submit").click(function(e)
        {
            e.preventDefault();

            getMainData();
        });

        var today = new Date().toISOString().split('T')[0];
        var threedays = new Date(new Date().setDate(new Date().getDate() - 3)).toISOString().split('T')[0];
        var oneweek = new Date(new Date().setDate(new Date().getDate() - 7)).toISOString().split('T')[0];
        var onemonth = new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0];

        $('.date').val(today);

        $('.date').keypress(function(e) {
            e.preventDefault();
        });

        $('#to').datepicker(
        {
            dateFormat: 'yyyy-mm-dd',
            maxDate: new Date(),
            autoClose: true,
            onSelect: function(date)
            {
                var maxDate = new Date(date);

                $("#from").datepicker(
                {
                    maxDate: maxDate,
                });

                if($('#from').val()>$('#to').val() || $('#from').val() == "")
                {
                    $("#from").val(new Date(maxDate).toISOString().split('T')[0]);
                }
            }
        });

        $("#from").datepicker(
        {
            dateFormat: 'yyyy-mm-dd',
            maxDate: new Date(),
            autoClose: true,
        });

        $("#today").on("click", function()
        {
            $('.btn-orange').removeClass("selected");
            $('#today').addClass("selected");
            $('.date').val(today);
        });

        $("#threedays").on("click", function()
        {
            $('.btn-orange').removeClass("selected");
            $('#threedays').addClass("selected");
            $('#from').val(threedays);
            $('#to').val(today);
        });

        $("#oneweek").on("click", function()
        {
            $('.btn-orange').removeClass("selected");
            $('#oneweek').addClass("selected");
            $('#from').val(oneweek);
            $('#to').val(today);
        });

        $("#onemonth").on("click", function()
        {
            $('.btn-orange').removeClass("selected");
            $('#onemonth').addClass("selected");
            $('#from').val(onemonth);
            $('#to').val(today);
        });

	});

	function prepareLocale()
	{
		locale['mainData.product'] =  "{!! __('app.bethistory.maindata.product') !!}";
		locale['mainData.turnover'] =  "{!! __('app.bethistory.maindata.turnover') !!}";
		locale['mainData.winloss'] =  "{!! __('app.bethistory.maindata.winloss') !!}";

        locale['mainData.date'] = "{!! __('app.bethistory.maindata.date') !!}";
        locale['mainData.amount'] = "{!! __('app.bethistory.maindata.amount') !!}";
        locale['mainData.method'] = "{!! __('app.bethistory.maindata.method') !!}";
        locale['mainData.type'] = "{!! __('app.bethistory.maindata.type') !!}";
        locale['mainData.status'] = "{!! __('app.bethistory.maindata.status') !!}";

        locale['mainData.type.d'] = "{!! __('app.bethistory.maindata.type.deposit') !!}";
        locale['mainData.type.w'] = "{!! __('app.bethistory.maindata.type.withdraw') !!}";

        locale['mainData.status.p'] = "{!! __('app.bethistory.maindata.status.pending') !!}";
        locale['mainData.status.a'] = "{!! __('app.bethistory.maindata.status.approved') !!}";
        locale['mainData.status.r'] = "{!! __('app.bethistory.maindata.status.rejected') !!}";

        locale['mainData.payment_type.d'] = "{!! __('app.bethistory.maindata.payment_type.bank_transfer') !!}";
        locale['mainData.payment_type.f'] = "{!! __('app.bethistory.maindata.payment_type.FPX') !!}";
	}

	function getMainData()
	{
		var containerId = "main-table";

		var data = utils.getDataTableDetails(containerId);
		data['type'] = $("#type").val();
		data['start_date'] = $("#from").val();
		data['end_date'] = $("#to").val();

		$("#main-spinner").show();
		$("#main-table").hide();
		$('#notes').hide();

		$.ajax({
			type: "GET",
			url: "/ajax/bet/history",
			data: data,
			success: function(data)
			{
				mainData = JSON.parse(data);
				loadMainData(containerId);
			}
		});
	}

	function loadMainData(containerId)
	{
		$("#main-spinner").hide();
	    $("#main-table").show();

	    var fields = [
	                    ["created_at",locale['mainData.date'],false,false]
	                    ,["amount",locale['mainData.amount'],false,true]
	                    ,["payment_type",locale['mainData.method'],false,true]
                        ,["type",locale['mainData.type'],false,true]
                        ,["status",locale['mainData.status'],false,true]
	                ];

	    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

	    if(table != null)
	    {   
	    	table.classList.remove("table-bordered");
	        $('#notes').show();
	        $("#total_record").hide();

		    var fieldDate = utils.getDataTableFieldIdx("created_at",fields);
		    var fieldAmount = utils.getDataTableFieldIdx("amount",fields);
		    var fieldPayment = utils.getDataTableFieldIdx("payment_type",fields);
            var fieldType = utils.getDataTableFieldIdx("type",fields);
            var fieldStatus = utils.getDataTableFieldIdx("status",fields);

		    for(var i = 1, row; row = table.rows[i]; i++)
	        {
                var amount = mainData.results[i - 1]['amount'];
                var paymentType = mainData.results[i - 1]['payment_type'];
                var type = mainData.results[i - 1]['type'];
                var status = mainData.results[i - 1]['status'];

                row.cells[fieldAmount].innerHTML = "";
                row.cells[fieldAmount].innerHTML = utils.formatMoney(amount);

                row.cells[fieldType].innerHTML = "";
                row.cells[fieldType].innerHTML = locale['mainData.type.'+type];

                row.cells[fieldPayment].innerHTML = "";
                row.cells[fieldPayment].innerHTML = locale['mainData.payment_type.'+paymentType];    

                row.cells[fieldStatus].innerHTML = "";
                row.cells[fieldStatus].innerHTML = locale['mainData.status.'+status];

                if(status == 'p')
                {
                    row.cells[fieldStatus].style.color = "grey"
                }
                else if(status == 'a')
                {
                    row.cells[fieldStatus].style.color = "green";
                }
                else
                {
                    row.cells[fieldStatus].style.color = "red";
                }
	        }
		}
	}

	function sortMainData()
	{
	    utils.prepareDataTableSortData(this.containerId,this.orderBy);

	    getMainData();
	}

	function pagingMainData()
	{
	    utils.prepareDataTablePagingData(this.containerId,this.page);

	    getMainData();
	}

</script>

<style>
	#main-table
	{
		padding: 0;
		font-size: 12px;
        color: black;
	}
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
    #main-table thead
    {
        background: linear-gradient(180deg,#a50037,#000);
        color: white;
    }
    #main-table tbody td
    {
        text-align: left !important;
    }
    #btn-submit
    {
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 5px;
        border:0;
        background: #CF2029;
        padding: 5px;
        min-width: 50px;
        cursor: pointer;
    }
</style>
@endsection

@section('content')

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">

            	<form id="mainForm">
            		<div class="form-group row" style="align-items: center">
                        <label class="col-sm-3 col-form-label">Transaction Type: <span style="color:red">*</span></label>
                        <div class="col-sm-3">
                            <select name="type" id="type">
                            	<option value="d">Deposit</option>
                            	<option value="w">Withdraw</option>
<!--                             	<option value="promo">Promotion</option>
                            	<option value="bonus">Bonus</option> -->
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-3 col-form-label">Transaction Date: <span style="color:red">*</span></label>
                        <div class="col-sm">
                            <input type="text" class="input-custom date" id="from" data-language="en" name="start_date">
    						<span style="color: #000">~</span>
                            <input type="text" class="input-custom date" id="to" data-language="en" name="end_date">
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm">
                            <div class="px-2 py-1" id="today" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	Today
                            </div>

                            <div class="px-2 py-1" id="threedays" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In 3 days
                            </div>

                            <div class="px-2 py-1" id="oneweek" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In a week
                            </div>

                            <div class="px-2 py-1" id="onemonth" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In a month
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm">
                            <button type="submit" id="btn-submit">Search</button>
                        </div>
                    </div>

            	</form>

            </div>
        </div>
    </div>

    <div style="background:white;border-radius:5px; margin-top: 10px;">
        <div class="py-4 px-2">

            	<div class="card">

		            <div id="main-spinner" class="card-body"></div>

		            <div id="main-table" class="card-body" style="padding: 5px;"></div>

		            <!-- <div id="notes" class="card-body"></div> -->

		        </div>
        </div>
    </div>
</div>

@endsection