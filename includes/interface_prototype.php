<?php

$prototype = new Interface_Prototype();

if (!$prototype->get_cache())
	$prototype->build_cache();
	
$prototype->display();

class Interface_Prototype {
	
	private $cache, $library='library', $O = array(
		'device'=>false,
		'clear_cache'=>false
	);
		
	function __construct($options=false)
	{
		//Write options set by ajax
		$this->set_options($this->get_options());
		//Write options passed by php
		$this->set_options($options);
		
		//Do detection if a device wasn't passed in options
		if (!$this->O['device'])
			$this->O['device'] = $this->detect_device();
				
		//Default to ipad on desktop client
		if ($this->O['device'] == 'desktop')
			$this->O['device'] = 'ipad';
	}
	
	//Return folder path with depth
	private function P($name, $depth = 0)
	{
		$library = $this->library;
		//Path is off of root
		if ($depth == 0)
		{
		  return "./$library/$name/";
		}
		else //Add ../ to path to escape depth
		{
		  $dots = '';
		  for ($i = 0; $i < $depth; $i++)
		  {
		    $dots .= '../';
		  }
		  return "$dots$library/$name/";
		}
	}
	
	public function detect_device()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		switch(true)
		{
			case (preg_match('/ipad/i', $user_agent));
			  $status = 'ipad';
			break;
			
			case (preg_match('/ipod/i', $user_agent)||preg_match('/iphone/i',$user_agent));
			  $status = 'iphone';
			break;

			default;
			  $status = 'desktop';
			break;
		}
		return $status;
	}
	
	//Is there a saved file for this device?
	public function get_cache()
	{
		if ($this->O['clear_cache'])
			return false;
		
		$device = $this->O['device'];
		$path = $this->P($device, 1);
		$path .= "$device.html";
		
		if (file_exists($path))
		{
			$this->cache = file_get_contents($path);
			return true;
		}
		return false;
	}
	
	public function display($echo = true)
	{
		$output = (empty($this->cache)) ?
			$this->template_404() :
			$this->cache;
		
		if ($echo){
			echo $output;
			return true;
		}
		return $output;
	}
	
	//Get the library files and compile into a single html file
	public function build_cache()
	{
		$device = $this->O['device'];
		$markup = array();
		$path = $this->P($device, 1);
		if (!file_exists($path))
			return false;
		
		//Organize the template files by orientation
		$handle = opendir($path);
		while ($filename = readdir($handle)){
			if (substr ($filename, -4) == ".lbi")
			{
				$orientation = 'default';
				if (preg_match('/landscape.lbi/', $filename))
				{
					$orientation = 'landscape';
				}
				else if (preg_match('/portrait.lbi/', $filename))
				{
					$orientation = 'portrait';
				}
				$id = preg_replace('/.lbi/', '', $filename);
				$filenames[$orientation][$id] = $filename;
			}
		}
		
		//Were any library files found?
		if (empty($filenames))
			return false;
			
		//Retrieve template files, prepare, organize into array
		foreach($filenames as $orientation=>$names)
		{
			foreach ($names as $id=>$filename)
			{
				$template = $path . $filename;
				
				$html = file_get_contents ($template);
			
				//remove metatags from .lbi files
				$html = preg_replace ("%<meta.*?>%sim", "", $html);
			
				//Correct the path to the image folder
				$replace = 'src="' . $this->P($device, 0) . 'images/';
				$html = str_replace('src="images/', $replace, $html);
			
				//Remove .htm extensions
				$html = preg_replace ("%\..?htm.*?\"%sim", '"', $html);
			
	      $markup[$orientation][$id] = $html;
			}
		}
		$this->cache = $this->create_cache($markup);
	}

	private function create_cache($markup)
	{
		$device = $this->O['device'];
		$markup = $this->construct_markup($markup);
		
		if (!$markup)
			return false;

		$path = ($this->P($device, 1) ) . "$device.html";
				
		$fp = fopen($path, 'w');
		fwrite($fp, $markup);
		fclose($fp);
	
		return $markup;
	}
	
	//Put together the html to be displayed on the page
	private function construct_markup($markup)
	{
		if (empty($markup))
			return false;
		
		$device = $this->O['device'];
		$output = "<div class='device_$device'>";
		foreach ($markup as $orientation => $templates)
		{
			$output .= "<div class='orientation_$orientation'>";
			foreach ($templates as $id => $markup)
			{
				$if_index = (preg_match('/[I|i]ndex/', $id)) ? '' : 'hide';
				$output .= "<div id='lp-$id' class='$if_index page'>";
				$output .= $markup;
				$output .= "</div>";
			}
			$output .= "</div>";
		}
		$output .= "</div>";
		return $output;
	}
	
	private function template_404()
	{
		$device = $this->O['device'];
		return <<<OUTPUT
			<div class="no_device">
				No template files were found for the device: $device.
			</div>
OUTPUT;
	}
	
	private function get_options()
	{
		$valid = array();
		foreach($_GET as $key=>$value)
		{
			if (isset($this->O[$key]))
			{
				$valid[$key] = $value;
			}
		}
		return $valid;
	}
	
	private function set_options($options)
	{
		if(!isset($options) || !is_array($options))
			return false;
			
		foreach($options as $key=>$value)
		{
			if(!empty($value) && !is_array($value))
			{
				$this->O[$key] = $value;
			}
		}
	}
}
?>
