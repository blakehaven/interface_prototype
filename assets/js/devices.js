$(document).ready(function(){
	//load the content
	getTemplates();
	
	//Switch orientation if needed
	detectOrientation();
	window.onorientationchange=detectOrientation;
	
	$("#desktop_navigation .select_device a").click(function(){
		$(this).siblings().show();
		$(this).hide();
		device = $(this).attr('href');
		getTemplates(device);
		return false;
	});
	
	$("#desktop_navigation .select_orientation a").click(function(){
		$(this).siblings().show();
		$(this).hide();
		orientation = $(this).attr('href');
		$('body').attr('id', orientation);
		return false;
	});
	
	bindClickArea();

});

function getTemplates(device)
{
	var get_device = (device != null) ? '?device='+device : '';
	//console.log(get_device);
	$.get("includes/library_prototype.php"+get_device, function(data){
		$("body #templates").html(data);
		bindClickArea();
	});
}

function detectOrientation()
{
	var orientation = window.orientation;
	//$('#default_navigation').html(orientation);
	switch(orientation)
	{	
		case 0:
				$('body').attr('id','current_portrait');
				break;
				
		case 180:
				$('body').attr('id','current_portrait');
				break;
				
		case 90:
				$('body').attr('id','current_landscape');
				break;
		
		case -90:	
				$('body').attr('id','current_landscape');
				break;
		
		default:
				$('body').attr('id','current_desktop_landscape');
				break;
	}
}

function bindClickArea()
{
	//Links are clickable
	$("area").unbind('click').click(function(){
		
		$('div.page').addClass('hide');
		
		//Display clicked page
		divid = 'lp-' + $(this).attr('href');
		console.log(divid);
		var alternatediv = (/-landscape/.test(divid)) ?
			divid.replace("-landscape", "-portrait") :
			divid.replace("-portrait", "-landscape");
			
		$('#'+alternatediv).removeClass('hide');
		$('#'+divid).removeClass('hide');
		return false;
	});
}
