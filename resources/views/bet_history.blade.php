@extends('layouts.app')

@section('head')
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();

		(function () {
            $("#from").datetimepicker({
                    timepicker: false,
                    format: 'Y/m/d',
                    theme: 'dark',
                    closeOnDateSelect: true,
                    // minDate: moment(),
                    maxDate: '0',
                    onChangeDateTime: function(dp,$input)
                    {
                    	startDate = $("#from").val();
                    	$("#to").val("");
                    	$("#to").datetimepicker({
                    		minDate: startDate
                    	})
                    }
                })

            $("#to").datetimepicker({
                    timepicker: false,
                    format: 'Y/m/d',
                    theme: 'dark',
                    maxDate: '0'
                })

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

		// getMainData();

	});

	function prepareLocale()
	{
		locale['mainData.product'] =  "{!! __('app.bethistory.maindata.product') !!}";
		locale['mainData.turnover'] =  "{!! __('app.bethistory.maindata.turnover') !!}";
		locale['mainData.winloss'] =  "{!! __('app.bethistory.maindata.winloss') !!}";
	}

	// function getMainData()
	// {
	// 	var containerId = "main-table";

	// 	var data = utils.getDataTableDetails(containerId);
	// 	data['prd_id'] = prdId;
	// 	data['start_date'] = $("#from").val();
	// 	data['end_date'] = $("#to").val();

	// 	$("#main-spinner").show();
	// 	$("#main-table").hide();
	// 	$('#notes').hide();

	// 	$.ajax({
	// 		type: "GET",
	// 		url: "/ajax/bet/products",
	// 		data: data,
	// 		success: function(data)
	// 		{
	// 			mainData = JSON.parse(data);
	// 			loadMainData(containerId);
	// 		}
	// 	});
	// }

	// function loadMainData(containerId)
	// {
	// 	$("#main-spinner").hide();
	//     $("#main-table").show();

	//     var fields = [
	//                     ["prd_name",locale['mainData.product'],false,false]
	//                     ,["turnover",locale['mainData.turnover'],false,true]
	//                     ,["win_loss",locale['mainData.winloss'],false,true]
	//                 ];

	//     table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

	//     if(table != null)
	//     {   
	//     	table.classList.remove("table-bordered");
	//         $('#notes').show();
	//         $("#total_record").hide();

	// 	    var fieldProduct = utils.getDataTableFieldIdx("prd_name",fields);
	// 	    var fieldTurnover = utils.getDataTableFieldIdx("turnover",fields);
	// 	    var fieldWinloss = utils.getDataTableFieldIdx("win_loss",fields);

	// 	    for(var i = 1, row; row = table.rows[i]; i++)
	//         {
	//         	var prdId = mainData.results[i-1]['prd_id'];
	//         	var prdName = mainData.results[i-1]['prd_name'];
	//         	var turnOver = mainData.results[i-1]['turnover'];
	//         	var winLoss = mainData.results[i-1]['win_loss'];

	//         	var product = document.createElement("a");
	//         	product.href = "/bet_history?prd_id="+prdId;
	//         	product.innerHTML = prdName;
	//         	row.cells[fieldProduct].innerHTML = "";
	//         	row.cells[fieldProduct].appendChild(product);

	//         	row.cells[fieldTurnover].innerHTML = "";
	//         	row.cells[fieldTurnover].innerHTML = utils.formatMoney(turnOver);

	//         	row.cells[fieldWinloss].innerHTML = "";
	//         	row.cells[fieldWinloss].innerHTML = utils.formatMoney(winLoss);
	//         }

	//         var sumFields = [      
	// 				            "turnover"
	// 				            ,"win_loss"
	// 				        ]; 

	//         utils.createSumForDataTable(table, mainData, mainData.results, fields, sumFields);

	//         for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
	//         {
	//         	var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
	//         	var totalWinloss = parseFloat(row.cells[fieldWinloss].innerHTML);

	//         	row.cells[fieldTurnover].innerHTML = "";
	//         	row.cells[fieldTurnover].innerHTML = utils.formatMoney(totalTurnover);

	//         	row.cells[fieldWinloss].innerHTML = "";
	//         	row.cells[fieldWinloss].innerHTML = utils.formatMoney(totalWinloss);
	//         }
	// 	}
	// }

	// function sortMainData()
	// {
	//     utils.prepareDataTableSortData(this.containerId,this.orderBy);

	//     getMainData();
	// }

	// function pagingMainData()
	// {
	//     utils.prepareDataTablePagingData(this.containerId,this.page);

	//     getMainData();
	// }

</script>

<style>
	#main-table
	{
		padding: 0;
		font-size: 12px;
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
</style>
@endsection

@section('content')

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">

            	<form id="mainForm">
            		<div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Transaction Type: <span style="color:red">*</span></label>
                        <div class="col-sm-3">
                            <select name="type">
                            	<option value="d">Deposit</option>
                            	<option value="w">Withdraw</option>
                            	<option value="promo">Promotion</option>
                            	<option value="bonus">Bonus</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Transaction Date: <span style="color:red">*</span></label>
                        <div class="col-sm-3">
                            <input type="date" id="start_dt" name="start_dt">
    						<span style="color: #000">to</span>
    						<input type="date" id="end_dt" name="end_dt">
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm">
                            <div class="px-2 py-1" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	Today
                            </div>

                            <div class="px-2 py-1" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In 3 days
                            </div>

                            <div class="px-2 py-1" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In a week
                            </div>

                            <div class="px-2 py-1" style="display:inline-block;cursor:pointer;white-space:nowrap;background:#F0F0F0;border-radius:5px;color:grey">
                            	In a month
                            </div>
                        </div>
                    </div>
            	</form>

            </div>
        </div>
    </div>

    <div style="background:white;border-radius:5px; margin-top: 10px;">
        <div class="py-4 px-2">
            <div class="container-fluid">

            	<div class="card">

		            <div id="main-spinner" class="card-body"></div>

		            <div id="main-table" class="card-body"></div>

		            <!-- <div id="notes" class="card-body"></div> -->

		        </div>
            </div>
        </div>
    </div>
</div>

@endsection