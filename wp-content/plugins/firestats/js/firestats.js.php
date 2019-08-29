<?php
require_once(dirname(__FILE__).'/../php/session.php');
$res = fs_resume_existing_session();
if ($res !== true) 
{
	echo "alert('firestats.js.php : $res');";
	return;
}
?>

/*
<?php
require_once(dirname(__FILE__).'/../php/init.php');
require_once(dirname(__FILE__).'/../php/utils.php');
require_once(dirname(__FILE__).'/../php/session.php');
// echo the sid for debug purposes
global $sid;
echo "Session ID : $sid\n";
if (fs_session_start($sid))
{
	global $FS_SESSION;
	dump_array($FS_SESSION);
}
else
{
	echo "Error initializing session : $sid\n";
}
function dump_array($arr, $prefix = '')
{
	foreach($arr as $k => $v)
	{
		if (!is_array($v))	echo $prefix . "$k = $v\n";
		else
		{
			echo "$k = \n";
			dump_array($v, $prefix.'    ');
		}
	}
}
?>
*/





function hideDialog(id)
{
	$(id).style.display='none';
	$('glasspane').style.display='none';
}

function showDialog(id)
{
	$(id).style.display='block';
	// don't use glasspane for silly explorer (z-order bug)
	if (!isIEXOrOlder(7))
	{
		$('glasspane').style.display='block';
	}
}

function confirmDialog(text,userdata,callback)
{
	$('fs_confirm_text').innerHTML = text;
	$('fs_confirm_no').onclick=function(){hideDialog('fs_confirmation_dialog');callback('no')};
	$('fs_confirm_yes').onclick=function(){hideDialog('fs_confirmation_dialog');callback('yes')};
	showDialog('fs_confirmation_dialog');
}


function openWindow(page, width, height)
{
	window.open (page, 'newwindow',"height="+height+",width="+width+",toolbar=no, menubar=no"+
			   ",scrollbars=no, location=no, directories=no, status=no");
}

function toggle_div_visibility(id)
{
	var disp = $(id).style.display;
	if (disp == "inline")
	{
		$(id).style.display = "none";
	}
	else
	{
		$(id).style.display = "inline";
	}
}


function hideFeedback()
{
	var e = document.getElementById("feedback_div");
	e.style.display = "none";
}

var messageTimerID;
function showFeedback(response, timeout)
{
	var e = $("feedback_zone");
	if (!e) return; // for dialogs etc
	if (!response.message) return;
	e.innerHTML = response.message;

	e = $("feedback_div");
	if (response.status == 'error')
	{
		e.style.background = '#f86262';
	}
	else
	{
		e.style.background = '#3aa9ff';
	}

	e.style.display = "block";
	
	if (timeout != null)
	{
		clearTimeout(messageTimerID);
		messageTimerID = setTimeout("hideFeedback()", timeout);
	}
}

function clearOptions(idlist,save,update)
{
	var a = idlist.split(',');
	a.each(function(item) 
	{
		var x = $(item);
		if(x.tagName.toLowerCase() == 'input')
		{
			x.value="";
		}
		else
		{
			x.innerHTML=txt;
		}
	});

	if(save == true) 
	{
		saveOptions(idlist,update);
	}
}

function saveOptions(idlist,update)
{
	saveOptions_imp(idlist,"fs",update);
}

function saveWpOptions(idlist,update)
{
	saveOptions_imp(idlist,"wp",update);
}

function saveOptions_imp(idlist,dest,update)
{
	// creates a list in the format list=key1,val1;key2,val2
	var a = idlist.split(',');
	var list = '';
	a.each(function(item) 
	{
		list += encodeURIComponent(item) + "," + encodeURIComponent($F(item)) + ";";
	});
	var params = 'action=' + 'saveOptions' + '&list=' + encodeURIComponent(list) + 
				 (update != null ? "&update="+encodeURIComponent(update) : "") +
				 "&dest=" + encodeURIComponent(dest);
	sendRequest(params);
}

function saveOptionValue(name, value, type)
{
	saveOptionImpl(name,value,type,null,"fs");
}

function saveOption(inputID, optionName, type)
{
	saveOptionImpl(optionName,$F(inputID), type,null,"fs");
}

function saveOption(inputID, optionName, type, update)
{
	saveOptionImpl(optionName, $F(inputID), type, update, "fs");
}

function saveWpOption(inputID, optionName, type, update)
{
	saveOptionImpl(optionName, $F(inputID),type, update, "wp");
}

function saveOptionImpl(optionName, txt, type, update, dest)
{
	var output = txt;
	var parsed = true;
	switch (type)
	{
	case 'positive_num':
		var n = parseInt(txt);
		parsed = n && n >= 0;
		if (!parsed)
		{
			showError("<?php print fs_r("Not a positive number : ") ?>" + txt);
		}
		break;
	case 'boolean':
		output = txt == 'on' ? 'true' : 'false';
		break;
	case 'string':
		break;
	default:
		showError('unsupported type ' + type);
		return;
	}

	if (parsed)
	{
		var params = 'action=' + 'saveOption' + '&key=' + encodeURIComponent(optionName) + 
					 "&value=" + encodeURIComponent(output) +
					 (update != null ? "&update="+encodeURIComponent(update) : "") +
					 "&dest=" + encodeURIComponent(dest);
		sendRequest(params);
	}
}

function showMessage(msg)
{
	try
	{	
		var x = {};
		x['status'] = 'ok';
		x['message'] = msg;
		showFeedback(x);
	}
	catch (e2)	
	{	
		// if even this failed, use alert.
		alert('error : ' + errorMessage);
	}
}

function showError(errorMessage)
{
	try
	{	
		var x = {};
		x['status'] = 'error';
		x['message'] = errorMessage;
		clearTimeout(messageTimerID);
		showFeedback(x);
	}
	catch (e2)	
	{	
		// if even this failed, use alert.
		alert('error : ' + errorMessage);
	}
}

function sendRequest(params,handler,prefix)
{
	sendRequest2(params,handler,prefix,false);
}

var fs_network_status_count = 0;
function sendRequest2(params,handler,prefix,silent)
{
	prefix = prefix != null ? prefix : '';
	if (!isIE6OrOlder())
	{
		if (!silent)
		{
			fs_network_status_count++;
			var net = $('network_status');
			if (net) net.style.display = "block";
		}
	}
	params += ("&sid=<?php echo $_REQUEST['sid']?>");
	if (typeof handler != 'function') handler = handleResponse;
	var ajaxUrl = "<?php echo fs_url('"+prefix+"php/ajax-handler.php').fs_get_request_suffix()?>";
	var myAjax = new Ajax.Request(
	ajaxUrl,
	{
		method: 'post', 
		parameters: params, 
		onComplete: function(response)
		{
			handler(response,silent);
			if (!isIE6OrOlder())
			{
				if (!silent)
				{
					fs_network_status_count--;
					if (fs_network_status_count == 0)
					{
						var net = $('network_status');
						if (net) net.style.display = "none";
					}
				}
			}
		}	
	});
}


function stripslashes(value)
{
	str = "" + value;
	str = str.replace(/\\"/g,'"' );
	str = str.replace(/\\\'/g,'\'' );
	return str;
}


function trapEnter(e, enterFunction)
{
	if (!e) e = window.event;
	if (e.keyCode == 13)
	{
		e.cancelBubble = true;
		if (e.returnValue) e.returnValue = false;
		if (e.stopPropagation) e.stopPropagation();
		if (enterFunction) eval(enterFunction);
		return false;
	} 
	else 
	{

		return true;
	}     
}


function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}


function validateIP(what) 
{
  if (what.search(/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/) != -1) 
	{
	  var myArray = what.split(/\./);
    if (myArray[0] > 255 || myArray[1] > 255 || myArray[2] > 255 || myArray[3] > 255)
  		return false;
		if (myArray[0] == 0 && myArray[1] == 0 && myArray[2] == 0 && myArray[3] == 0)
    	return false;
    return true;
  }
  else
		return false;
}

function isIE6OrOlder()
{
    return isIEXOrOlder(6);
}

function isIEXOrOlder(x)
{
	var ua = navigator.userAgent;
	var i = ua.indexOf("MSIE");
	if (i != -1)
	{
		var ver = parseFloat(ua.substring(i + 5, i + 8));
        return ver <= x;
	}
    return false;
}


function applyResponse(data)
{
	applyResponse2(data,false);
}

function applyResponse2(data,silent)
{
	var fields = data['fields'];
	if (fields)
	{
		for (var key in fields) 
		{
			try
			{	
				var txt =  stripslashes(fields[key]);
				var e = $(key);
				if(e)
				{
					if (data['type'] && data['type'][key] == 'tree') 
					{
						replaceTree(key, txt);
					}
					else
					if(e.tagName.toLowerCase() == 'input')
					{
						e.value=txt;
					}
					else
					{
						e.innerHTML=txt;
					}
				}
				else
				{
					if (!silent) alert('Element not found: ' + key);
				}
			}
			catch(e)
			{
				alert(dump(e));	
			}
		}
	}
	
	var styles = data['styles'];
	for (var key in styles) 
	{
		var style  = styles[key];
	
		for (var prop in style) 
		{
			try
			{
				var e = $(key);
				if(e) 
				{	
					e.style[prop]=style[prop];
				}
				else
				{
					if (!silent) alert('Element not found: ' + key);
				}
			}
			catch(ex)
			{
				alert(ex);
			}
		}
	}

}

function handleResponse(response,silent)
{
	try
	{
		eval("var r = " + response.responseText);
	}
	catch (e)
	{
		showError("error evaluating response : Response text:<br/>" + response.responseText + 
				  "<br/><br/>Exception : <br/>" + dump(e) );	
		return;
	}

	try
	{
		if (r.status == 'error')
		{	
			showError(r.message);
		}
		else if (r.status == 'ok')
		{
			//alert(dump(r));
			switch (r.action)
			{
			case 'importCounterize':
				showFeedback(r,5000);
				applyResponse2(r,silent);
				$('import_counterize').disabled = false;
			break;
			case 'createNewDatabase':
			case 'upgradeDatabase':
			case 'attachToDatabase':
			case 'installDBTables':
				if (r.db_status == 'ok')
				{
					window.location.reload(); // no ideal, but it will have to do for now.
				}
				else
				{
					showFeedback(r,5000);
					applyResponse2(r,silent);
				}
			break;
			default:
				showFeedback(r,5000);
				applyResponse2(r,silent);
			}

			if (r.refresh == 'true')
			{
				window.location.reload();
			}
			
			if (r.send_request)
			{
				sendRequest(r.send_request, handleResponse);
			}
		}
		else if (r.status == 'session_expired')
		{
			alert("<?php fs_e('Session expired, press ok to reload')?>");
			window.location.reload();
		}
		else
		{
			showError('Unknown responst type ' + r.status);
		}
	}
	catch (e)
	{
		showError('error processing response : ' + dump(e));	
	}
}

function replaceTree(id,tree)
{
	var str = stripslashes(tree);
	var tree = convertTreeString(str);
	var treeDiv = $(id);
	treeDiv.replaceChild(tree, treeDiv.getElementsByTagName('div')[0]);
}

// selects the select item with the speficied text
function selectByText(selectId, text)
{
	var children = $(selectId).childNodes;
	for (var i = 0; i < children.length; i++)
	{
		var child = children[i];
		if (child.text == text)
		{
			$(selectId).selectedIndex = i;
			break;
		}
	}
}
