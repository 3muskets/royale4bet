@extends('my_profile')

@section('head')
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();

		prdId = utils.getParameterByName('prd_id');

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

		getMainData();

	});

	function prepareLocale()
	{
		locale['mainData.product'] =  "{!! __('app.bethistory.maindata.product') !!}";
		locale['mainData.turnover'] =  "{!! __('app.bethistory.maindata.turnover') !!}";
		locale['mainData.winloss'] =  "{!! __('app.bethistory.maindata.winloss') !!}";
	}

	function getMainData()
	{
		var containerId = "main-table";

		var data = utils.getDataTableDetails(containerId);
		data['prd_id'] = prdId;
		data['start_date'] = $("#from").val();
		data['end_date'] = $("#to").val();

		$("#main-spinner").show();
		$("#main-table").hide();
		$('#notes').hide();

		$.ajax({
			type: "GET",
			url: "/ajax/bet/products",
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
	                    ["prd_name",locale['mainData.product'],false,false]
	                    ,["turnover",locale['mainData.turnover'],false,true]
	                    ,["win_loss",locale['mainData.winloss'],false,true]
	                ];

	    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

	    if(table != null)
	    {   
	    	table.classList.remove("table-bordered");
	        $('#notes').show();
	        $("#total_record").hide();

		    var fieldProduct = utils.getDataTableFieldIdx("prd_name",fields);
		    var fieldTurnover = utils.getDataTableFieldIdx("turnover",fields);
		    var fieldWinloss = utils.getDataTableFieldIdx("win_loss",fields);

		    for(var i = 1, row; row = table.rows[i]; i++)
	        {
	        	var prdId = mainData.results[i-1]['prd_id'];
	        	var prdName = mainData.results[i-1]['prd_name'];
	        	var turnOver = mainData.results[i-1]['turnover'];
	        	var winLoss = mainData.results[i-1]['win_loss'];

	        	var product = document.createElement("a");
	        	product.href = "/bet_history?prd_id="+prdId;
	        	product.innerHTML = prdName;
	        	row.cells[fieldProduct].innerHTML = "";
	        	row.cells[fieldProduct].appendChild(product);

	        	row.cells[fieldTurnover].innerHTML = "";
	        	row.cells[fieldTurnover].innerHTML = utils.formatMoney(turnOver);

	        	row.cells[fieldWinloss].innerHTML = "";
	        	row.cells[fieldWinloss].innerHTML = utils.formatMoney(winLoss);
	        }

	        var sumFields = [      
					            "turnover"
					            ,"win_loss"
					        ]; 

	        utils.createSumForDataTable(table, mainData, mainData.results, fields, sumFields);

	        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
	        {
	        	var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
	        	var totalWinloss = parseFloat(row.cells[fieldWinloss].innerHTML);

	        	row.cells[fieldTurnover].innerHTML = "";
	        	row.cells[fieldTurnover].innerHTML = utils.formatMoney(totalTurnover);

	        	row.cells[fieldWinloss].innerHTML = "";
	        	row.cells[fieldWinloss].innerHTML = utils.formatMoney(totalWinloss);
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
	}

</style>
@endsection

@section('details')

<div class="body">
	<div class="account-info section" data-section="profile">
	    <div class="title">
	        <span class="span-title"></span>
	    </div>
	    <div class="profile-container">
	        <div class="section-body">
	            <div id="content-top-bar" class="tab-container clearfix">
	            </div>
	            <div data-tab="history" id="ember349" class="section-tab tab-focus ember-view">
	                <div class="form-container filter-header"
	                    style="display: flex;min-width: 70px;flex-wrap: wrap;height: auto;padding: 15px;">
	                    <div class="form-group">
	                        <div class="form-element inline from-to-container">
	                            <label>{{ __('app.bethistory.range') }}:</label>
	                            <div class="datepicker-div">
	                                <div>
	                                    <div class="datepicker-block from">
	                                        <input name="from" id="from" placeholder="{{ __('app.bethistory.date.from') }}" type="text" autocomplete="off">
	                                        <span class="clear icon-icon-clear"></span>
	                                    </div>
	                                </div>
	                                <span>-</span>
	                                <div>
	                                    <div class="datepicker-block to">
	                                        <input name="to" id="to" placeholder="{{ __('app.bethistory.date.to') }}" type="text" autocomplete="off">
	                                        <span class="clear icon-icon-clear"></span>
	                                    </div>
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="refresh-button-container" style="margin-bottom:13px;">
	                        <button class="btn btn1" onclick="getMainData()">{{ __('app.bethistory.btn.search') }}</button>
	                    </div>
	                </div>
	                <div calss="card" style="border:none;background:transparent;">
	                	<div id="main-spinner" class="card-body"></div>
	                	<div class="card-body" id="main-table"></div>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
</div>

@endsection