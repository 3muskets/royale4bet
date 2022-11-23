@extends('my_profile')

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
		locale['mainData.subject'] = "{{ __('app.sent.maindata.sucject') }}";
		locale['mainData.date'] = "{{ __('app.sent.maindata.date') }}";
		locale['mainData.close'] = "{{ __('app.sent.maindata.close') }}";
	}

	function getMainData()
	{
		var containerId = "main-table";

		var data = utils.getDataTableDetails(containerId);

		$("#main-spinner").show();
		$("#main-table").hide();
		$('#notes').hide();

		$.ajax({
			type: "GET",
			url: "/ajax/message/sent",
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
	                    ["subject",locale['mainData.subject'],false,false]
	                    ,["created_at",locale['mainData.date'],false,false]
	                ];

	    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);
	    table.setAttribute("style", "border-collapse: separate; border-spacing: 0 10px;");

	    if(table != null)
	    {      
	        $('#notes').show();
	        $("#total_record").hide();

	        var fieldSubject = utils.getDataTableFieldIdx("subject",fields);
	        var fieldDate = utils.getDataTableFieldIdx("created_at",fields);

	        var x = 1;

	        for(var i = 1, row; row = table.rows[i]; i++)
	        {
	        	//for every n+1 row
	        	if(i%2 == 1)
	        	{
	        		y = i;

	        		if(i>2)
	        		{
	        			//to get mainData index from 0,1,2...
	        			y = i-x;
	        			x++;
		            }

		            table.rows[i].className = "collapsed row-collapse";
		        	table.rows[i].setAttribute("style", "cursor:pointer;");
		        	table.rows[i].setAttribute("data-parent", "#main-table");
	                table.rows[i].setAttribute("data-toggle", "collapse");
	                table.rows[i].setAttribute("data-target", "#message"+mainData.results[y-1]["id"]);
	                table.rows[i].id = mainData.results[y-1]["id"];
	                table.rows[i].date = row.cells[fieldDate];

	                var div = document.createElement("tr");
	                div.className = "collapse";
	                div.id = "message"+mainData.results[y-1]["id"];

	                var divMessage = document.createElement("td");
	                divMessage.colSpan = 2;
	                divMessage.setAttribute("style", "text-align:center;border-right: 1px solid #5a5a5a !important;");

	                var divContainer = document.createElement("div");
	                divContainer.className = "container";
	                divContainer.setAttribute("style", "width:100% !important");

	                var divRow = document.createElement("div");
	                divRow.className = "row m-0";

	                var divCol = document.createElement("div");
	                divCol.className = "col-md";

	                var divColMsg = document.createElement("div");
	                divColMsg.setAttribute("style", "border:none;min-height:100px;text-align:left;padding:5px 10px;");
	                divColMsg.innerHTML = mainData.results[y-1]["message"];

	               	divContainer.appendChild(divRow);
	               	divRow.appendChild(divCol);
	               	divCol.appendChild(divColMsg);
	               	divMessage.appendChild(divContainer);
	                div.appendChild(divMessage);

	                $("#main-table tbody tr").eq(i-1).after(div);
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
		color: #c7cbd7;
	}
	#main-table table
	{
		overflow: hidden;
	}
	#main-table th
	{
		height: 30px;
		border: none;
		background: #a71f67;
		width: 100%;
	}
	#main-table th:first-child
	{
		width: 37% !important;
		text-align: left;
		border-radius: 3px 0 0 3px;
	}
	#main-table th:last-child
	{
		border-radius: 0 3px 3px 0;
	}
	#main-table tbody tr:nth-child(odd)
	{
		background: #313131;
	}
	#main-table td
	{
		border: none;
		border: 1px solid #5a5a5a;
	}
	#main-table td:first-child
	{
		text-align: left;
		border-radius: 3px 0 0 3px;
		border-right: none;
	}
	#main-table td:last-child
	{
		border-radius: 0 3px 3px 0;
	}
	.row-collapse:after {
	    font-family: 'FontAwesome';
	    content: "\f077";
	    font-size: 12px;
	    color: #5a5a5a;
	    position: absolute;
	    right: 1rem;
	    margin-top: 10px;
	    padding-left: 10px;
	    border-left: 1px solid #5a5a5a;
	}
	.row-collapse.collapsed:after {
	    /* symbol for "collapsed" panels */
	    content: "\f078";
	    font-size: 12px;
	    color: #5a5a5a;
	    position: absolute;
	    right: 1rem;
	    margin-top: 10px;
	    padding-left: 10px;
	    border-left: 1px solid #5a5a5a;
	}
	.collapsing
    {
      	-webkit-transition-delay: 0s;
      	transition-delay: 0s;
      	transition: height 0s ease;
    }  
</style>
@endsection

@section('details')

<div class="body">
    <div class="account-info section" data-section="profile">
        <div class="title">
            <span class="span-title"></span>
            <span class="unreadmsg"></span>
        </div>
        <div class="messages-container">
            <div class="section-body">
                <div id="content-top-bar" class="tab-container clearfix">
                </div>
                <div class="inbox div-scrollable" style="overflow-y: auto;">
			        <div class="card" style="background:transparent;border:none;">
			        	<div id="main-spinner" class="card-body"></div>
			        	<div id="main-table" class="card-body accordion"></div>
			        </div>
			    </div>
			</div>
		</div>
    </div>
</div>

@endsection