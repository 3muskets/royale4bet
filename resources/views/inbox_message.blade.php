@extends('layouts.app')

@section('head')
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();

		type = utils.getParameterByName('type');

		utils.createSpinner("main-spinner");

		getMainData();

	});

	function prepareLocale()
	{
		locale['mainData.subject'] = "{{ __('app.inbox.maindata.sucject') }}";
		locale['mainData.date'] = "{{ __('app.inbox.maindata.date') }}";
		locale['mainData.reply'] = "{{ __('app.inbox.maindata.reply') }}";
		locale['mainData.close'] = "{{ __('app.inbox.maindata.close') }}";
		locale['mainData.markall'] = "{{ __('app.inbox.maindata.markall') }}";
		locale['mainData.yourmessage'] = "{{ __('app.inbox.maindata.yourmessage') }}";

		locale['info'] = "{!! __('common.modal.info') !!}";
	    locale['success'] = "{!! __('common.modal.success') !!}";
	    locale['error'] = "{!! __('common.modal.error') !!}";

		locale['tooltip.check'] = "<input type='checkbox' id='checkAll' style='vertical-align:middle' onclick='checkAll(this);'>";
	}

	function getMainData()
	{
		var containerId = "main-table";

		var data = utils.getDataTableDetails(containerId);
		data['type'] = type;

		$("#main-spinner").show();
		$("#main-table").hide();
		$('#notes').hide();

		$.ajax({
			type: "GET",
			url: "/ajax/message/inbox",
			data: data,
			success: function(data)
			{
				mainData = JSON.parse(data);

				console.log(mainData);

				loadMainData(containerId);
			}
		});
	}

	function loadMainData(containerId)
	{
		$("#main-spinner").hide();
	    $("#main-table").show();

	    var fields = [
	    				["#",locale['tooltip.check'],false,false]
	                    ,["subject",locale['mainData.subject'],false,false]
	                    ,["created_at",locale['mainData.date'],false,false]
	                    ,["action","Remarks",false,false]
	                ];

	    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

	    if(table != null)
	    {
	    	$("#notes").show();
	    	$("#total_record").hide();

	    	var fieldCheckbox = utils.getDataTableFieldIdx("#",fields);
	    	var fieldSubject = utils.getDataTableFieldIdx("subject",fields);
	    	var fieldDate = utils.getDataTableFieldIdx("created_at",fields);
	    	var fieldAction = utils.getDataTableFieldIdx("action",fields);

	    	for (var i = 1, row; row = table.rows[i]; i++) 
	    	{
	    		var id = mainData.results[i-1]["id"];
	    		var isRead = mainData.results[i - 1]["is_read"];

	    		if(!isRead)
	    		{
	    			row.cells[fieldSubject].style.fontWeight = "900";
	    			row.cells[fieldDate].style.fontWeight = "900";
	    			row.cells[fieldSubject].style.fontWeight = "900";
	    		}

	    		var checkbox = document.createElement("input");
	    		checkbox.id = "checkbox"+id;
	    		checkbox.type = "checkbox";
	    		checkbox.name = "check[]";
	    		checkbox.setAttribute("style", "vertical-align:middle");

	    		row.cells[fieldCheckbox].innerHTML = "";
	    		row.cells[fieldCheckbox].append(checkbox);

	    		row.cells[fieldAction].innerHTML = "";

	    		row.cells[fieldSubject].style.cursor = "pointer";
	    		row.cells[fieldDate].style.cursor = "pointer";
	    		row.cells[fieldSubject].onclick = showMessage;
	    		row.cells[fieldDate].onclick = showMessage;

	    	}
	    }
	}

	function checkAll(e)
	{
	    var checked = e.checked;

	    if (checked) 
	    {
	        $('input[name="check[]"]').not(":disabled").prop('checked', true);

	    } 
	    else 
	    {
	        $('input[name="check[]"]').prop('checked', false);
	    }
	}

	function markAsRead()
	{
		var dateField = this.date;
		this.style.fontWeight = "normal";
		dateField.style.fontWeight = "normal";

		var readIcon = $(dateField).find("i");
		readIcon[0].className = "fa fa-envelope-open";
		readIcon[0].setAttribute("style", "position:absolute;right:38%;color:#808080;");

		var msgId = this.id;

		$.ajax({
			type: "POST",
			url: "/ajax/message/inbox/read",
			data: {msgId:msgId}

		});
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

	function showMessage()
	{
		utils.showModal();
	}

</script>

<style>
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
    #main-table table
    {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        color: #c9c9c9;
    }
    
    #main-table table th
    {
        background: linear-gradient(180deg,#393854,#131228) !important;
    }

    #main-table table tbody tr:nth-child(even)
    {
        box-shadow: 
                    inset 0px 15px 8px -15px #77a5eb,
                    inset 0px -15px 8px -15px #77a5eb; 
    }

    #main-table table tr th, #main-table table tr td
    {
        padding-left: 10px;
        padding-right: 10px;
    }

    #main-table .btn
    {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #3e3a8e;
        border-radius: 5px;
        border: 1px solid transparent;
        line-height: normal;
        height: auto;
        opacity: 1;
        color: #fff;
    }

    #main-table .btn:hover
    {
        filter: brightness(1.1);
    }

    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }
</style>
@endsection

@section('content')

<div class="card">

    <div class="card-header">
        <span>Messages</span>
    </div>

    <div class="card-body" id="main-table"></div>

    <div class="card-body" id="main-spinner"></div>

</div>

@endsection