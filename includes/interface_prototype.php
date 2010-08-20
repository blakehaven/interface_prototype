<?php
include('device_detect.php');

//Specific device requested?
$device = (isset($_GET['device']))?
	$_GET['device'] :
	mobile_device_detect();
	
//Clear cache requested?
$clear_cache = (isset($_GET['clear_cache']))?
	$_GET['clear_cache'] :
	false;
	
$prototype = new Interface_Prototype($device, $clear_cache);

if (!$prototype->get_cache())
{
	$prototype->build_cache();
}
$prototype->display();


class Interface_Prototype {
	
	private $cache, $device, $clear_cache, $library = 'library',
		$no_results = '<div class="no_device">No template files were found for this device.</div>';	
	
	function __construct($device, $clear_cache)
	{
		$this->device = $device;
		$this->clear_cache = $clear_cache;
		
		//Default to ipad on desktop client
		if ($this->device == 'desktop')
			$this->device = 'ipad';
		
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
	
	//Is there a saved file for this device?
	public function get_cache()
	{
		if ($this->clear_cache)
			return false;
			
		$device = $this->device;
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
			$this->no_results :
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
		$device = $this->device;
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
			
				//start div with filename
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
		$markup = $this->construct_markup($markup);
		
		if (!$markup)
			return false;

		$path = ($this->P($this->device, 1) ) . "$this->device.html";
				
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
		
		$output = "<div class='device_$this->device'>";
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
}
?>
