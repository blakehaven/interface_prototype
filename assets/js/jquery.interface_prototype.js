jQuery(function ($) {
	$.fn.interfacePrototype = function (opt) {
	  
		var self = this, options =  $.extend({
			path_php: 'includes/interface_prototype.php',
			ajax_call: '.navigation .ajax_call',
			select_orientation: '.navigation .select_orientation',
			templates: '.templates'
		}, opt);
		
		//Return the options selector
		var T = function(target, additional_selectors){
			var as = (additional_selectors) ? ' ' + additional_selectors : '';
			return '#' + self.attr('id') + ' ' + options[target] + as;
		};
		
		//Return the selector
		var S = function(selector){ 
			var append = (selector) ?
				' ' + selector:
				'';				
			return '#' + self.attr('id') + append;
		};
		
		this.construct = function(){
			
			//load the content
			self.get_templates();
			
			//Switch orientation if needed
			self.detect_orientation();
			window.onorientationchange=self.detect_orientation();
			
			//When clicked display this device
			$(T('ajax_call', 'a')).click(function(){
				params = $(this).attr('href');
				self.get_templates(params);
				return false;
			});
			
			//When clicked display given orientation
			$(T('select_orientation', 'a')).click(function(){
				$(this).siblings().show();
				$(this).hide();
				orientation = $(this).attr('href');
				$(S()).attr('class', orientation);
				return false;
			});
			//self.bind_click_area();
		};

		this.get_templates = function(params){
			//Additional params?
			var get_device = '';
			if (params)
				get_device = '?'+params;
			
			//Get the device template
			$.get(options.path_php + get_device, function(data){
				$(T('templates')).html(data);
				self.bind_click_area();
			});
		};

		this.bind_click_area = function()
		{
			//Links are clickable
			$(S('area')).unbind('click').click(function(){

				$(S('.page')).addClass('hide');
				
				//Display clicked page
				divid = 'lp-' + $(this).attr('href');
				
				var alternatediv = (/landscape/.test(divid)) ?
					divid.replace("landscape", "portrait") :
					divid.replace("portrait", "landscape");
				
				$('#'+alternatediv).removeClass('hide');
				$('#'+divid).removeClass('hide');
				return false;
			});
		};
		
		this.detect_orientation = function(){
			var window_or = window.orientation;
			var orientation = 'desktop_landscape';
			switch(window_or)
			{
				case 0:
						orientation = 'portrait';
						break;
				
				case 180:
						orientation = 'portrait';
						break;
				
				case 90:
						orientation = 'landscape';
						break;
		
				case -90:	
						orientation = 'landscape';
						break;
		
				default:
						orientation = 'desktop_landscape';
						break;
			}
			$(S()).attr('class', orientation);
		};
		
		this.construct();
		return self;
		
	};
});



