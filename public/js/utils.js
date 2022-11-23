(function(root) 
{
	//FUNCTION DECLARATION
	var utils = {
		
		'isMobile' : isMobile,

		//data table
		'createDataTable' : createDataTable,
		'createBetDetailsDataTable' : createBetDetailsDataTable,
		'prepareDataTableSortData' : prepareDataTableSortData,
		'prepareDataTablePagingData' : prepareDataTablePagingData,
		'getDataTableFieldIdx' : getDataTableFieldIdx,
		'getDataTableDetails' : getDataTableDetails,
		'resetDataTableDetails' : resetDataTableDetails,
		'createSumForDataTable' : createSumForDataTable,

		'addClass' : addClass,
		'removeClass' : removeClass,

		//submit button
		'startLoadingBtn' : startLoadingBtn,
		'stopLoadingBtn' : stopLoadingBtn,

		'showModal' : showModal,
		'createSpinner' : createSpinner, //spinner indicator

		'formatMoney' : formatMoney,
		'formatInt' : formatInt,
		'formatCurrencyInput' : formatCurrencyInput,
		'formatCurrencyInputNegative' : formatCurrencyInputNegative,
		'formatted_num' : formatted_num,

		'generateModalMessage' : generateModalMessage,

		//Logging
		'generateLogData' : generateLogData,

		//get qs
		'getParameterByName' : getParameterByName,

		//get date in dd/mm/yy
		'getToday' : getToday,
		'getOneWeek' : getOneWeek,
		'getFifteenDays' : getFifteenDays,
		'getMonth' : getMonth,
		'formattedDate' : formattedDate,
		'datepickerStart' : datepickerStart,
		'datepickerEnd' : datepickerEnd,

		'getCurrentDateTime' : getCurrentDateTime,
		'getKironCurrentDateTime' : getKironCurrentDateTime,
		'getCurrentDateTimeWithoutDay' : getCurrentDateTimeWithoutDay,
		'padLeft' : padLeft,

		'getPreviousTier' : getPreviousTier,

		'myTimer' : myTimer,
		'getMemberId' : getMemberId,

		'resizeTable' : resizeTable,
	};
	
	root.utils = utils;

	function isMobile()
	{
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
			return true;

		return false;
	}

	function myTimer() 
    {
        currentTime = new Date().getTime();
        comingSoon  = new Date(arrTime[0]).getTime();
        // Find the distance between now and the count down date
        var distance = comingSoon - currentTime;
        var minutes = ("0" + Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).slice(-2);
        var seconds = ("0" + Math.floor((distance % (1000 * 60)) / 1000)).slice(-2);

        var countDown = minutes+":"+seconds;
        if(countDown == "00:00")
        {
            arrTime.splice(0,1);
            betNow.splice(0,1);
            getEvents();
            getPendingBet();
        }

        if(betNow!=[])
        {
            $("#"+comingSoon).css("opacity",1).html(countDown);
            $("#"+betNow[0]).css({"opacity":1, "background-color":"green"}).html("Now");
        }
    }

    function getMemberId() 
    {
    	var url = window.location.href;

    	var token = location.href.split("token=")[1];

    	var memberId = -1;

    	if (token != undefined) 
        {
        	var base64Url = token.split('.')[1];

        	var decodedValue = JSON.parse(window.atob(base64Url));

        	memberId = decodedValue['merchant-user'];
        }

        return memberId;
    }

	function createDataTable(containerId,dataSet,fields,callbackSort,callbackPaging)
	{
		//fields structure
		// 0 - field name
		// 1 - field title
		// 2 - allow order
		// 3 - align right

		//container
	    var div = document.getElementById(containerId);
	    div.innerHTML = "";

	    //handle exception
	    if(dataSet.length == 0)
	    {
	    	div.innerHTML = locale['utils.datatable.invaliddata'];

			return null;
	    }

	    //table data
	    var data = dataSet.results;

	    if(data.length > 0)
	    {
		    //order by data
		    var orderBy = div.orderBy;
	    	var orderType = div.orderType;

	    	//paging data
		    var page = div.page;
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;

		    if(page == undefined)
		    {
		    	page = 1;
		    	div.pagination = page;
		    }

		    var ttlPage = Math.ceil(dataSize / pageSize);

		    if(ttlPage < 1)
		    	ttlPage = 1;

		    var aryPage = [];

		    for(var i = -2 ; i < 3 ; i++)
		    {
		    	var validPage = false;

		  		var tmpPage = page + i;

		    	if(tmpPage >= 1 && tmpPage <= ttlPage)
		    	{
		    		validPage = true;
		    	}

		    	if(validPage)
		    		aryPage.push(tmpPage);
		    }

		    // console.log(aryPage);

		    //paging top
	    	var divRow = document.createElement("div");
	    	divRow.className = "row m-0";
			div.appendChild(divRow);

			var divColLeft = document.createElement("div");
	    	divColLeft.className = "col-md-6";
	    	divRow.appendChild(divColLeft);

	    	var navTop = document.createElement("nav");
			divColLeft.appendChild(navTop); 

			createDataTablePaging(containerId,navTop,page,aryPage,callbackPaging,ttlPage);

			var divColRight = document.createElement("div");
			divColRight.id = "total_record";
	    	divColRight.className = "col-md-6 text-right";
	    	divColRight.innerHTML = locale['utils.datatable.totalrecords'] + " : " + dataSize;
	    	divRow.appendChild(divColRight);

	    	//table container
	    	var divTableContainer = document.createElement("div");
	    	divTableContainer.className = "table-responsive";
	    	div.appendChild(divTableContainer); 

		    //table
		    var table = document.createElement("table");
			table.className = "table-resize table-sm mb-2 w-100";	
			divTableContainer.appendChild(table); 

			//table header
			var tHead = table.createTHead();
			var row = tHead.insertRow(0); 

			for (i = 0; i < fields.length; i++)
		    {
		    	var fieldName = fields[i][0];
		    	var fieldTitle = fields[i][1];
		    	var allowOrder = fields[i][2];

		    	var th = document.createElement('th');
				th.innerHTML = fieldTitle;

				if(allowOrder)
				{
					th.containerId = containerId;
					th.orderBy = fieldName;

					utils.addClass(th,'sorting');

					th.onclick = callbackSort;

					if(orderBy == fieldName)
					{
						if(orderType == "desc")
							utils.addClass(th,'sorting-desc');
						else
							utils.addClass(th,'sorting-asc');
					}
				}

				row.appendChild(th);
		    } 

		    //table data
			var tBody = table.createTBody();
			
		    for (i = 0; i < data.length; i++)
		    {
		        row = tBody.insertRow(i);
		        
		        for (j = 0; j < fields.length; j++)
		        {
		        	var alignRight = fields[j][3];

		            var cell = row.insertCell(j);

		            if(alignRight)
		            	cell.style.textAlign = "right";

		            cell.innerHTML = data[i][fields[j][0]];                          
		        }                   
		    }

		    //paging bottom
		    var navBottom = document.createElement("nav");
			div.appendChild(navBottom); 

			createDataTablePaging(containerId,navBottom,page,aryPage,callbackPaging,ttlPage);

			return table;
		}
		else
		{
			div.innerHTML = locale['utils.datatable.norecords'];

			return null;
		}
	}

	function createBetDetailsDataTable(containerId,dataSet,fields,callbackSort,callbackPaging)
	{
		//fields structure
		// 0 - field name
		// 1 - field title
		// 2 - allow order
		// 3 - align right

		//container
	    var div = document.getElementById(containerId);
	    div.innerHTML = "";

	    //handle exception
	    if(dataSet.length == 0)
	    {
	    	div.innerHTML = locale['utils.datatable.invaliddata'];

			return null;
	    }

	    //table data
	    var data = dataSet.results;

	    if(data.length > 0)
	    {
		    //order by data
		    var orderBy = div.orderBy;
	    	var orderType = div.orderType;

	    	//paging data
		    var page = div.page;
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;

		    if(page == undefined)
		    {
		    	page = 1;
		    	div.pagination = page;
		    }

		    var ttlPage = Math.ceil(dataSize / pageSize);

		    if(ttlPage < 1)
		    	ttlPage = 1;

		    var aryPage = [];

		    for(var i = -2 ; i < 3 ; i++)
		    {
		    	var validPage = false;

		  		var tmpPage = page + i;

		    	if(tmpPage >= 1 && tmpPage <= ttlPage)
		    	{
		    		validPage = true;
		    	}

		    	if(validPage)
		    		aryPage.push(tmpPage);
		    }

		    // console.log(aryPage);

		    //paging top
	    	var divRow = document.createElement("div");
	    	divRow.className = "row m-0";
			div.appendChild(divRow);

			var divColLeft = document.createElement("div");
	    	divColLeft.className = "col-md-6";
	    	divRow.appendChild(divColLeft);

	    	var navTop = document.createElement("nav");
			divColLeft.appendChild(navTop); 

			createDataTablePaging(containerId,navTop,page,aryPage,callbackPaging,ttlPage);

			var divColRight = document.createElement("div");
			divColRight.id = "total_record";
	    	divColRight.className = "col-md-6 text-right";
	    	divColRight.innerHTML = locale['utils.datatable.totalrecords'] + " : " + dataSize;
	    	divRow.appendChild(divColRight);

	    	//table container
	    	var divTableContainer = document.createElement("div");
	    	divTableContainer.className = "table-responsive lt-head-wrap";
	    	div.appendChild(divTableContainer);

	    	var extraDiv = document.createElement('div');
	    	divTableContainer.appendChild(extraDiv);

		    //table
		    var table = document.createElement("table");
			table.className = "table resize-table table-bordered table-striped table-sm mb-2";	
			extraDiv.appendChild(table); 

			//table header
			var tHead = table.createTHead();
			tHead.className = "lt-head";
			var row = tHead.insertRow(0); 

			for (i = 0; i < fields.length; i++)
		    {
		    	var fieldName = fields[i][0];
		    	var fieldTitle = fields[i][1];
		    	var allowOrder = fields[i][2];

		    	var th = document.createElement('th');
		    	th.className = "th-column";
				th.innerHTML = fieldTitle;

				// if(allowOrder)
				// {
				// 	th.containerId = containerId;
				// 	th.orderBy = fieldName;

				// 	utils.addClass(th,'sorting');

				// 	th.onclick = callbackSort;

				// 	if(orderBy == fieldName)
				// 	{
				// 		if(orderType == "desc")
				// 			utils.addClass(th,'sorting-desc');
				// 		else
				// 			utils.addClass(th,'sorting-asc');
				// 	}
				// }

				row.appendChild(th);
		    } 

		    //table data
			var tBody = table.createTBody();
			tBody.className = "lt-body";
			
		    for (i = 0; i < data.length; i++)
		    {
		        row = tBody.insertRow(i);
		        row.className = "lt-row";
		        
		        for (j = 0; j < fields.length; j++)
		        {
		        	var alignRight = fields[j][3];

		            var cell = row.insertCell(j);
		            cell.className = "lt-cell";

		            if(alignRight)
		            	cell.style.textAlign = "right";

		            cell.innerHTML = data[i][fields[j][0]];                          
		        }                   
		    }

		    //paging bottom
		    var navBottom = document.createElement("nav");
			div.appendChild(navBottom); 

			createDataTablePaging(containerId,navBottom,page,aryPage,callbackPaging,ttlPage);

			return table;
		}
		else
		{
			div.innerHTML = locale['utils.datatable.norecords'];

			return null;
		}
	}

	function createDataTablePaging(containerId,nav,page,aryPage,callbackPaging,ttlPage)
	{
		if(ttlPage == 1)
			return;

		var ul = document.createElement("ul");
		ul.className = "pagination pagination pagination-sm mb-2";
		nav.appendChild(ul);

		//for << and < 
		if(page > 1)
		{
			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link"><<</span>';
			ul.appendChild(li); 

			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = page - 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link"><</span>';
			ul.appendChild(li); 
		}

		//for page number
		for(var i = 0 ; i < aryPage.length ; i++)
	    {
	    	var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = aryPage[i];
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">' + aryPage[i] + '</span>';

			if(aryPage[i] == page)
				utils.addClass(li,"active");

			ul.appendChild(li); 
	    }

	    //for > and >> 
	    if(page != ttlPage)
		{
			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = page + 1;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">></span>';
			ul.appendChild(li); 

			var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = ttlPage;
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">>></span>';
			ul.appendChild(li); 
		}

		//spacing
		var li = document.createElement('li');
		li.innerHTML = '&nbsp;&nbsp;';
		ul.appendChild(li); 

		//for dropdown
		var li = document.createElement("li");

		var dd = document.createElement("select");
		dd.className = "form-control";
		dd.style.height = "36px";
		dd.style.fontSize = "12px";
		dd.style.color = "#fff";
		dd.style.border = "none";
		dd.style.background = "rgba(143, 145, 147, .5)";

		dd.containerId = containerId;
		
		dd.onchange = function ()
			{ 
				this.page = parseInt(this.options[this.selectedIndex].value);
				
				callbackPaging.call(this);
			 };

		for(var i = 0 ; i < ttlPage ; i++)
	    {
	    	var option = document.createElement("option");
			option.text = i + 1;
			option.value = i + 1;
			option.style.color = "#fff";
			option.style.background = "black";
			dd.add(option);
	    }

		dd.selectedIndex = page - 1;

		li.appendChild(dd);
		ul.appendChild(li); 
	}

	function prepareDataTableSortData(containerId,orderBy)
	{
		var div = document.getElementById(containerId);

    	var prevOrderBy = div.orderBy;
    	var prevOrderType = div.orderType;

		if(orderBy == prevOrderBy)
	    {
	        if(prevOrderType == "desc")
	        {
	            div.orderType = "asc";
	        }
	        else
	        {
	            div.orderType = "desc";
	        }
	    }
	    else
	    {
	        div.orderType = "desc";
	    }

	    div.orderBy = orderBy; 
	}


	function prepareDataTablePagingData(containerId,pageNo)
	{
		var div = document.getElementById(containerId);

    	div.page = pageNo;
	}

	function getDataTableFieldIdx(name,fields)
	{
	    for (i = 0; i < fields.length; i++)
	    {
	        if(name == fields[i][0])
	            return i;
	    }

	    return 0;
	}

	function getDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

	    var data = {
                page : div.page
                ,order_by : div.orderBy
                ,order_type : div.orderType
                };

        return data;
	}

	function resetDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

		div.page = null;
    	div.orderBy = null;
    	div.order_type = null;
	}

	function createSumForDataTable(table,dataSet,dataSetTotal,fields,sumFields)
	{
		//1st field in table can't be sum, as reserved for title "Total"
		//if more than 1 page will have an extra row for current page total

		var isMultiPage = false;

		//check number of page
	    var data = dataSet.results;

	    if(data.length > 0)
	    {
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;

		    if(dataSize > pageSize)
		    	isMultiPage = true;
		}
		else
		{
			return;
		}

		var footer = table.createTFoot();

		//1st row total
		var row = footer.insertRow(0); 
		var cell = row.insertCell(0);

		if(isMultiPage)
			cell.innerHTML = "<b>" + locale['utils.datatable.pagetotal'] + "</b>";
		else
			cell.innerHTML = "<b>" + locale['utils.datatable.total'] + "</b>";

		for(var i = 1 ; i < fields.length ; i++)
		{
			var cell = row.insertCell(i);

			var isSumField = false;
			var fieldName = "";

			//check whether the field need to sum
			for(var j = 0 ; j < sumFields.length ; j++)
			{
				if(utils.getDataTableFieldIdx(sumFields[j],fields) == i)
				{
					isSumField = true;
					fieldName = sumFields[j];
					break;
				}
			}

			if(isSumField)
			{
				var total = 0;

				cell.style.textAlign = "right";

				for(var h = 0 ; h < data.length ; h++)
				{	
					var figure = parseFloat(data[h][fieldName]);
					
					if(isNaN(figure))
						figure = 0;

					total += figure;
				}

				cell.innerHTML = total;
			}
		}

		//2nd row total
		if(isMultiPage)
		{
			var row = footer.insertRow(1); 
			var cell = row.insertCell(0);

			cell.innerHTML = "<b>" + locale['utils.datatable.total'] + "</b>";

			for(var i = 1 ; i < fields.length ; i++)
			{
				var cell = row.insertCell(i);

				var isSumField = false;
				var fieldName = "";

				//check whether the field need to sum
				for(var j = 0 ; j < sumFields.length ; j++)
				{
					if(utils.getDataTableFieldIdx(sumFields[j],fields) == i)
					{
						isSumField = true;
						fieldName = sumFields[j];
						break;
					}
				}

				if(isSumField)
				{
					var total = 0;

					cell.style.textAlign = "right";

					var figure = dataSetTotal[0][fieldName];

					if(isNaN(figure))
						figure = 0;
					
					cell.innerHTML = figure;
				}
			}
		}
	}
	
	function addClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");
	    if(arr.indexOf(name) == -1) 
	    {
	        element.className += " " + name;
	    }
	}

	function removeClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");

	    var idx = arr.indexOf(name);

	    if(idx >= 0) 
	    {
	        arr.splice(idx,1);
	    }

	    element.className = arr.join(" ");
	}

	function startLoadingBtn(element,overlayContainer) 
	{
		if (element != "") 
		{
			var btn = document.getElementById(element);

		    var ladda = Ladda.create(btn);
			ladda.start();
		}
			
		//create overlay
		if(overlayContainer)
		{
			var div = document.createElement('div');
			div.id = overlayContainer + "_overlay";
	    	div.style.backgroundColor = "black";
	    	div.style.width = "100%";
	    	div.style.height = "100%";
	    	div.style.top = "0";
	    	div.style.left = "0";
	    	div.style.opacity = "0.2";
	    	div.style.position = "absolute";
	    
	    	document.getElementById(overlayContainer).appendChild(div);
		}
	}

	function stopLoadingBtn(element,overlayContainer) 
	{
		if (element != "") 
		{
			var btn = document.getElementById(element);

		    var ladda = Ladda.create(btn);
			ladda.stop();
		}
			
		//remove overlay
		if(overlayContainer)
		{
			var overlay = document.getElementById(overlayContainer + "_overlay");
			overlay.parentNode.removeChild(overlay);
		}
	}

	function showModal(contentTitle,contentBody,type,callbackClose)
	{
	    var modal = document.createElement("div");
	    modal.className = "modal fade";
	    modal.setAttribute("role", "dialog");     

	    var dialog = document.createElement("div");

	    if(type == 1)
		{
			dialog.className = "modal-dialog modal-success";
		}
		else 
		{
			dialog.className = "modal-dialog modal-danger";
		}
	    
	    dialog.setAttribute("role", "document");   
	    modal.appendChild(dialog);              

	    var content = document.createElement("div");
	    content.className = "modal-content";
	    dialog.appendChild(content);   

	    var header = document.createElement("div");
	    header.className = "modal-header";
	    content.appendChild(header);   

	    var title = document.createElement("h4");
	    title.className = "modal-title";
	    title.innerHTML = contentTitle;
	    header.appendChild(title);

	    var btnX = document.createElement("button");
	    btnX.className = "close";
	    btnX.setAttribute("data-dismiss", "modal");
	    btnX.setAttribute("style", "color:#fff;");
	    btnX.innerHTML = "×";
	    header.appendChild(btnX);

	    var body = document.createElement("div");
	    body.className = "modal-body";
	    body.innerHTML = contentBody;

	    content.appendChild(body); 

	    var footer = document.createElement("div");
	    footer.className = "modal-footer";
	    content.appendChild(footer); 

	    var btnClose = document.createElement("button");
	    btnClose.className = "btn btn-sm btn-secondary modal-btn";
	    btnClose.setAttribute("data-dismiss", "modal");
	    btnClose.innerHTML = "OK";
	    footer.appendChild(btnClose);

	    $(modal).modal('show');

	    if(callbackClose)
	    {
	    	$(modal).on('hidden.bs.modal', function () {
			    callbackClose();
			});
	    }
	    
	    // speed up focus on close btn
	    setTimeout(function (){
	        $(btnClose).focus();
	    }, 150);

	    //fail safe to focus
	    $(modal).on('shown.bs.modal', function() {
			$(btnClose).focus();
		});
	}

	function createSpinner(element) 
	{
		var spinner = document.getElementById(element);

		var div = document.createElement('div');
		div.className = "sk-wave";
		spinner.appendChild(div);

		var rect;

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect1";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect2";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect3";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect4";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect5";
		div.appendChild(rect);

	}

	function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") 
	{
	  try {
	    decimalCount = Math.abs(decimalCount);
	    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

	    const negativeSign = amount < 0 ? "-" : "";

	    let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
	    let j = (i.length > 3) ? i.length % 3 : 0;

	    return negativeSign 
	    	+ (j ? i.substr(0, j) + thousands : '') 
	    	+ i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" 
	    	+ thousands) 
	    	+ (decimalCount ? decimal 
	    	+ Math.abs(amount - i).toFixed(decimalCount).slice(2) : ""
	    	);
		  } 
		  catch (e) 
		  {
		    console.log(e)
		  }
	}

	function formatInt(amount, decimalCount = 0, decimal = ".", thousands = ",") 
	{
	  try {
	    decimalCount = Math.floor(decimalCount);
	    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

	    const negativeSign = amount < 0 ? "-" : "";

	    let i = parseInt(amount = Math.floor(Number(amount) || 0).toFixed(decimalCount)).toString();
	    let j = (i.length > 3) ? i.length % 3 : 0;

	    return negativeSign 
	    	+ (j ? i.substr(0, j) + thousands : '') 
	    	+ i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" 
	    	+ thousands) 
	    	+ (decimalCount ? decimal 
	    	+ Math.floor(amount - i).toFixed(decimalCount).slice(2) : ""
	    	);
		  } 
		  catch (e) 
		  {
		    console.log(e)
		  }
	}

	function formatCurrencyInput(input)
	{        
	    $(input).on( "keyup", function( event )
	    {   
	        // When user select text in the document, also abort.
	        var selection = window.getSelection().toString();
	        if ( selection !== '' )
	        {
	            return;
	        }
	                
	        // When the arrow keys are pressed, abort.
	        if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 )
	        {
	            return;
	        }
	                
	        var $this = $( this );
	                
	        // Get the value.
	        var input = $this.val();
	                
	        var input_length = input.length;

	        // check for decimal
			if (input.indexOf(".") >= 0) 
			{			  	
			  	// get position of first decimal to prevent multiple decimals from being entered
			    var decimal_pos = input.indexOf(".");

			    // split number by decimal point
			    var left_side = input.substring(0, decimal_pos);//before decimal point 
			    var right_side = input.substring(decimal_pos);//after decimal point

			    left_side = left_side.replace(/[^/\d/\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");

			    right_side = right_side.replace(/[^/\d/]+/g, "");  	    
			    
			    // Limit decimal to only 2 digits
			    right_side = right_side.substring(0, 2);

			    input = left_side + "." + right_side;
			} 
			else 
			{
			    input = input.replace(/[^\d\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   
			}

	        $this.val( function()
	        {
	           //trimming leading zero and dot symbol
	           while(input.substring(0,1) === '00' || input.substring(0,1) === '.')
	           {
	           		input = input.substring(1);
	           }

	           return input;
	        });
	    });
	}

	function formatCurrencyInputNegative(input)
	{        
		$(input).on( "keydown", function( event )
	    {	
	    	var input = $(this).val();

	     	if( input.indexOf("-") == 0 && $.inArray( event.keyCode, [109,189] ) !== -1)
	        {
	        	return false;
	        }

	   	});

	    $(input).on( "keyup", function( event )
	    {   
	        // When user select text in the document, also abort.
	        var selection = window.getSelection().toString();
	        if ( selection !== '' )
	        {
	            return;
	        }
	                
	        // When the arrow keys are pressed, abort.
	        if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 )
	        {
	            return;
	        }
	                
	        var $this = $( this );
	                
	        // Get the value.
	        var input = $this.val();
	                
	        var input_length = input.length;

	        
	        // check for decimal
			if (input.indexOf(".") >= 0) 
			{			  	
			  	// get position of first decimal to prevent multiple decimals from being entered
			    var decimal_pos = input.indexOf(".");

			    // split number by decimal point
			    var left_side = input.substring(0, decimal_pos);//before decimal point 
			    var right_side = input.substring(decimal_pos);//after decimal point

			    left_side = left_side.replace(/[^/\d/\.-]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");

			    right_side = right_side.replace(/[^/\d/]+/g, "");  	    
			    
			    // Limit decimal to only 2 digits
			    right_side = right_side.substring(0, 2);

			    input = left_side + "." + right_side;
			} 
			else 
			{
			    input = input.replace(/[^\d\.-]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   
			}

			if (input.indexOf("-") > 0)
	        {
           		input = input.replace(/[^\d\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	        }

	        $this.val( function()
	        {
	           //trimming leading zero and dot symbol
	           while(input.substring(0,1) === '0' || input.substring(0,1) === '.')
	           {
	           		input = input.substring(1);
	           }


	           return input;
	        });
	    });
	}

	function generateModalMessage(container,type,contentBody)
	{
	    container = "#" + container;

	    $(container).html("");
	    $(container).removeClass("bg-success");
	    $(container).removeClass("bg-danger");

	    if(type == 1)
	    {
	        $(container).addClass("bg-success");

	        $(container).html(contentBody);
	    }
	    else
	    {
	        $(container).addClass("bg-danger");

	        if(Array.isArray(contentBody)) //is array
	        {
	            var ul = document.createElement("ul");

	            for(var i = 0 ; i < contentBody.length ; i++)
	            {
	                var li = document.createElement("li");
	                li.innerHTML = contentBody[i];
	                ul.appendChild(li);
	            }

	            $(container).append(ul);
	        }
	        else
	        {
	            $(container).html(contentBody);
	        }
	    }
	    
	    $(container).show();
	}

	function generateLogData(aryLogFields)
	{
		//aryLogFields - contains id of elements to be put into json
		
	    var obj = {};

	    for (i = 0; i < aryLogFields.length; i++)
	    {
	        var id = aryLogFields[i];

	        obj[id] = $("#" + id).val();
	    }

	    return JSON.stringify(obj);
	}

	function formatted_num(pad, trans, pad_pos)
	{
		if (typeof trans === 'undefined') 
		    return pad;
		if (pad_pos == 'l')
		{
		    return (pad + trans).slice(-pad.length);
		}
		else 
		{
		    return (trans + pad).substring(0, pad.length);
		}
	}

	function getParameterByName(name,url) 
	{
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, '\\$&');
	    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
	        results = regex.exec(url);
	    if (!results) return null;
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, ' '));
	} 

	function getCurrentDateTime()
    {
    	var toGMT = +8;

        var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

        var currentHours = padLeft(now.getHours(),2,'0');
        var currentMinutes = padLeft(now.getMinutes(),2,'0');
        var currentSeconds = padLeft(now.getSeconds(),2,'0');

        // var day = locale['utils.datetime.day.' + now.getDay()];

        var gmtSymbol = toGMT >= 0 ? '+' : '-';

        var str = padLeft(now.getDate(),2,'0') 
        	+ '/' + padLeft(now.getMonth() + 1,2,'0')
        	+ '/' + now.getFullYear() 
            + '&nbsp;' + currentHours 
            + ':' + currentMinutes 
            + ':' +currentSeconds;
            // +'&nbsp;' + 'GMT ' + gmtSymbol + Math.abs(toGMT) + ':00';

        return str;
    }

    function getCurrentDateTimeWithoutDay()
    {
    	var toGMT = +8;

        var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

        var currentHours = padLeft(now.getHours(),2,'0');
        var currentMinutes = padLeft(now.getMinutes(),2,'0');
        var currentSeconds = padLeft(now.getSeconds(),2,'0');

        var str = now.getFullYear() 
            + '-' + padLeft(now.getMonth() + 1,2,'0')
            + '-' + padLeft(now.getDate(),2,'0') 
            + ' ' + currentHours 
            + ':' + currentMinutes;

        return str;
    }

    function getKironCurrentDateTime()
    {
    	var toGMT = +7;

        var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

		return now;
    }

    function padLeft(str, len, prefix)
    {
        return Array(len-String(str).length+1).join(prefix||'0')+str;
    }

    function getPreviousTier(tierCode,previousTierCount)
	{
	    if(tierCode.length >= 7)
	    {
	        if(previousTierCount == 1)
	            return tierCode.slice(0,5);
	        else if(previousTierCount == 2)
	            return tierCode.slice(0,3); 
	    }
	    else if(tierCode.length >= 5)
	    {
	        if(previousTierCount == 1)
	            return tierCode.slice(0,3);
	    }

	    return '';
	}

	function formattedDate(d)
	{
		var d = new Date(d);

		var year = d.getFullYear();
		var month = ("00" + (d.getMonth() + 1).toString()).slice(-2);
		var day = ("00" + (d.getDate()).toString()).slice(-2);
					
		return day + '/' + month + '/' + year;
	}

	function getToday()
	{
	    var toGMT = +9;

	    var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month = (1 + d.getMonth()).toString();
	    month = month.length > 1 ? month : '0' + month;

	    var day = d.getDate().toString();
	    day = day.length > 1 ? day : '0' + day;

	    var str =  day + '/' + month + '/' + d.getFullYear() 
	    return str;
	}

	function getOneWeek() 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var one_week = new Date(d.setDate(d.getDate() - 7));
	    var one_week_date = ("00" + (one_week.getDate()).toString()).slice(-2) + '/' + ("00" + (one_week.getMonth() + 1).toString()).slice(-2) + '/' + one_week.getFullYear();

	    return one_week_date;
	}

	function getFifteenDays() 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var fifteen = new Date(d.setDate(d.getDate() - 15));
	    var fifteen_date = ("00" + (fifteen.getDate()).toString()).slice(-2) + '/' + ("00" + (fifteen.getMonth() + 1).toString()).slice(-2) + '/' + fifteen.getFullYear();

	    return fifteen_date;
	}

	function getMonth(noOfMonths) 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month_date;
	    var checkYear = d.getFullYear();
	    var checkMonth = d.getMonth();
	    var checkDate = d.getDate();

	    if (checkMonth == 0) 
	    {
	        checkYear = checkYear - 1;
	        checkMonth = checkMonth - noOfMonths + 12 ;
	    } 
	    else 
	    {
	        checkMonth = checkMonth - noOfMonths; 
	    }

	    var isValidDateResult = isValidDate(checkYear, checkMonth, checkDate);

	    if (isValidDateResult) 
	    {
	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    } 
	    else 
	    {
		    if (checkMonth == 1) 
		    { 
		        month_date = d.setDate(getDateDay(checkYear, checkMonth, checkDate));
		    } 
		    else 
		    {
		        month_date = d.setDate(d.getDate() - 1);
		    }

	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    }

	    month_date = new Date(month_date);
	    var the_month_date = ("00" + (month_date.getDate().toString())).slice(-2) + '/' + ("00" + (month_date.getMonth() + 1).toString()).slice(-2) + '/' + month_date.getFullYear();

	    return the_month_date;
	}

	function isValidDate(year, month, day) 
	{	
	    var d = new Date(year, month, day);

	    if (d.getFullYear() == year && d.getMonth() == month && d.getDate() == day) 
	    {
	        return true;
	    }
	    return false;
	}

	function getDateDay(year, month, day) 
	{
	    var lastDayOfTheMonth = new Date(year, month + 1, 0).getDate();
	    if (day > lastDayOfTheMonth) 
	    {
	        return lastDayOfTheMonth;
	    }
	    return day;
	}

	//set the datepicker option
	function options()
	{
	   var opts = {
	        dateFormat: "dd/mm/yy", 
	        altFormat: "yy-mm-dd",
	        maxDate: '0', 
	        changeMonth: true,
	        changeYear: true
	    };
	    
	    return opts; 
	}

	function datepickerStart(s_date,e_date,pass_date,set_date)
	{
	    var opts = options();

	    if(set_date == '')
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                $(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                if($("#" + s_date).val() == '')
	                {
	                    $(this).datepicker('option', 'maxDate', new Date());
	                }
	                else
	                {
	                    $(this).datepicker('option', 'maxDate', $('#' + e_date).val());
	                }
	            }
	        }, opts)).datepicker("setDate", set_date);
	    }
	}

	function datepickerEnd(s_date,e_date,pass_date,set_date)
	{
	    var opts = options();

	    if(set_date == '')
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                $(this).datepicker('option', 'minDate', $('#' + s_date).val());
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function() 
	            {
	                $(this).datepicker('option', 'minDate', $('#' + s_date).val());
	            }
	        }, opts)).datepicker("setDate", set_date);
	    }

	    
	}

	function resizeTable()
	{
		if ($('.resize-table').length > 0) {
		    var resizeTableList = [];
		    $('.resize-table').each(function (i, e) {
		        resizeTableList.push((function (e) {
		            var width = undefined;
		            return function () {
		                // resize
		                var changeWidth = 120;
		                var toRender = false;
		                if (width === undefined || width > $(e).width()) {
		                    // to small
		                    while (true) {
		                        var th = $(e).find('th:not(.resize-table-hide)');
		                        if (th.length <= 2) {
		                            break;
		                        }
		                        var n = $(e).find('th:not(.resize-table-hide)').length;
		                        if ($(e).find('th:not(.resize-table-hide):nth-child(' + n + ')').width() >= changeWidth) {
		                            break;
		                        }
		                        $(e).find('th:first-child,td:first-child').show();
		                        $(e).find('th:not(.resize-table-hide):nth-child(' + n + ')').addClass('resize-table-hide');
		                        $(e).find('tr:not(.expend) td:not(.resize-table-hide):nth-child(' + n + ')').addClass('resize-table-hide');
		                        $(e).find('tr.expend td').attr('colspan', n - 1);
		                        toRender = true;
		                    }
		                }
		                if (width === undefined || width < $(e).width()) {
		                    // to big
		                    while (true) {
		                        var th = $(e).find('th:not(.resize-table-hide)');
		                        if ($(th[0]).width() + (th.length + 1) * changeWidth > $(e).width()) {
		                            break;
		                        }
		                        var n = $(e).find('th.resize-table-hide').length;
		                        if (n === 0) {
		                            $(e).find('th:first-child,td:first-child').hide();
		                            break;
		                        }
		                        $(e).find('th:nth-last-child(' + n + ')').removeClass('resize-table-hide');
		                        $(e).find('tr:not(.expend) td:nth-last-child(' + n + ')').removeClass('resize-table-hide');
		                        $(e).find('tr.expend td').attr('colspan', th.length + 1);
		                        toRender = true;
		                    }
		                }
		                if (width === undefined || toRender) {
		                    if ($(e).find('th.resize-table-hide').length > 0) {
		                        $(e).find('th:first-child,td:first-child').show();
		                    } else {
		                        $(e).find('th:first-child,td:first-child').hide();
		                    }
		                    renderTableExpendContent(e);
		                }
		                width = $(e).width();
		            }
		        })(e));
		    });

		    function renderTableExpendContent(table) {
		        var label = [];
		        $(table).find('th.resize-table-hide').each(function (i, e) {
		            label.push($(e).text().trim());
		        });
		        $(table).find('tr.expend').each((i, trExpend) => {
		            $(trExpend).find('dl').empty();
		            $(trExpend).prev().find('td.resize-table-hide').each(function (i, e) {
		                $(trExpend).find('dl').append($('<dt>' + (label[i] ? label[i] + ':' : '') + '</dt>')).append(
		                    '<dd>' + $(e).html() + '</dd>'
		                );
		            });
		        });
		    }
		    setInterval(function () {
		        resizeTableList.forEach(function (f) {
		            f();
		        })
		    }, 300);
		    $('.resize-table td:first-child').on('click', function () {
		        if ($(this).find('i').hasClass('icon-icon-arrow-right')) {
		            $(this).find('i').attr('class', 'fa icon-icon-arrow-down');
		            $(this).parents('tr')
		                .after($('<tr class="lt-row expend"><td class="lt-cell align-left" colspan="' +
		                    $(this).parents('.resize-table').find('th:not(.resize-table-hide)').length +
		                    '"><div class="row"><dl class="dl-horizontal" style="margin:1em 0;"></dl></div></td></tr>'));
		            renderTableExpendContent($(this).parents('.resize-table'));
		        } else {
		            $(this).find('i').attr('class', 'fa icon-icon-arrow-right');
		            $(this).parents('tr').next().remove();
		        }
		    });
		}
	}

}(this));