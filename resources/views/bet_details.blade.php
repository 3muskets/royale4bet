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
		locale['mainData.txn_id'] =  "{!! __('app.bethistory.maindata.txn_id') !!}";
		locale['mainData.debit'] =  "{!! __('app.bethistory.maindata.debit') !!}";
		locale['mainData.credit'] =  "{!! __('app.bethistory.maindata.credit') !!}";
		locale['mainData.created_at'] = "{!! __('app.bethistory.maindata.created_at') !!}";
		locale['mainData.game'] = "{!! __('app.bethistory.maindata.game') !!}";
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
			url: "/ajax/bet/details",
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
	    				["#",'',false,false]
	                    ,["txn_id",locale['mainData.txn_id'],true,false]
	                    ,["game_name",locale['mainData.game'],true,false]
	                    ,["debit",locale['mainData.debit'],false,false]
	                    ,["credit",locale['mainData.credit'],false,false]
	                    ,["timestamp",locale['mainData.created_at'],true,false]
	                ];

	    table = utils.createBetDetailsDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

	    if(table != null)
	    {   
	    	$("#total_record").hide();
	    	table.classList.remove("table-bordered");
	        $('#notes').show();

		    var fieldExpand = utils.getDataTableFieldIdx("#",fields);
		    var fieldTxn = utils.getDataTableFieldIdx("txn_id",fields);
		    var fieldGame = utils.getDataTableFieldIdx("game_name",fields);
		    var fieldDebit = utils.getDataTableFieldIdx("debit",fields);
		    var fieldCredit = utils.getDataTableFieldIdx("credit",fields);
		    var fieldCreatedAt = utils.getDataTableFieldIdx("timestamp",fields);

		    for(var i = 1, row; row = table.rows[i]; i++)
	        {
	        	var txnId = mainData.results[i-1]['txn_id'];

	        	var iconExpand = document.createElement("i");
	        	iconExpand.className = "fa icon-icon-arrow-right";

	        	row.cells[fieldExpand].classList.remove("lt-cell");
	        	row.cells[fieldExpand].innerHTML = "";
	        	row.cells[fieldExpand].appendChild(iconExpand);

	        	var txnField = document.createElement("a");
	        	txnField.setAttribute("href", "#");
	        	txnField.setAttribute("onclick", "showDetailsModal("+i+")");
	        	txnField.innerHTML = txnId;
	        	row.cells[fieldTxn].innerHTML = "";
	        	row.cells[fieldTxn].appendChild(txnField);

	        }
		}

		utils.resizeTable();

	}

	var clicked = true;

	function showDetailsModal(rowId)
	{

		//temporary use
        $('#modalDetails .card').html("");
        var iframe = document.createElement('iframe');
        iframe.setAttribute('src','123');
        iframe.style.height = "480px";
        $('#modal-json').hide();

        $('#modalDetails .modal-content').css('width', '850px');
        $("#modalDetails").modal('show');
        $("#modalDetails #modal-table").html('Under Maintanence');
        $('#modalDetails #modal-table').css({'font-size': '40px','text-align':'center','height':'550px','justify-content':'center'});
        //temporary use

/*	    if(clicked)
	    {
	        clicked = false;
	        var username = mainData.results[rowId - 1]["username"];
	        var txnId = mainData.results[rowId - 1]["txn_id"];
	        var roundId = mainData.results[rowId - 1]["round_id"];
	        var memberId = mainData.results[rowId - 1]["member_id"];
	        // var prdId = mainData.results[rowId - 1]["prd_id"];
	        var gameId = mainData.results[rowId - 1]["game_id"];
	        var configId = mainData.results[rowId - 1]["config_id"];
	        var creditDate = mainData.results[rowId - 1]["credit_date"];

	        var data = {
	                    txn_id : txnId
	                    ,member_id: memberId
	                    ,prd_id: prdId
	                    ,game_id: gameId
	                    ,round_id: roundId
	                    ,config_id: configId
	                    ,credit_date: creditDate
	                    };

	        $.ajax({
	            type: "GET",
	            url: "/ajax/bet/transaction/details",
	            data: data,
	            success: function(data) 
	            {
	     
	                $('#modalDetails .card').html("");
	                var iframe = document.createElement('iframe');
	                iframe.setAttribute('src',data);
	                iframe.style.height = "480px";
	                $('#modal-json').hide();
	    
	                $('#modalDetails .card').html(iframe);
	                $('#modalDetails .modal-content').css('width', '850px');
	                $("#modalDetails").modal('show');

	            },
	            complete: function()
	            {
	                clicked = true;
	            }
	        });
	    }*/
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
                <div data-tab="history " class="section-tab tab-focus ember-view div-scrollable">
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
                            <button class="btn btn1" onclick="window.location.href='/bet_history'">{{ __('app.bethistory.btn.back') }}</button>
                        </div>
                    </div>
					<div class="card" style="border:none; background:transparent;">
						<div id="main-spinner" class="card-body"></div>
						<div id="main-table" class="card-body"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="modalDetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.betdetail.title') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>            
            </div>
            <div class="modal-body">
                <div class="card" id="modal-table"></div>
            </div>        
        </div>
    </div>
</div>
@endsection