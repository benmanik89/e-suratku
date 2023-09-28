<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class
require("../cprofile/Smarty.class.php");
class Template extends Smarty
{
	var $template_dir, $compile_dir, $Smarty, $memberThemes;
	public function __construct()
	{
		//Constructor
		global $config;
		$this->Smarty = new Smarty();
		$cacheFolder = $config['compile']['topdir'];

		if ($this->memberThemes!="")
			$this->Smarty->template_dir = $this->memberThemes;
		else
			$this->Smarty->template_dir	= $config['main']['themes'];
		
		if ($this->memberThemes!="")
		{
			if ($this->is_mobile(strtolower($_SERVER['HTTP_USER_AGENT'])))
				$this->Smarty->compile_dir = $cacheFolder.$this->memberThemes."/mobile";
			else
				$this->Smarty->compile_dir = $cacheFolder.$this->memberThemes."/website";
		}
		else
		{
			if ($this->is_mobile(strtolower($_SERVER['HTTP_USER_AGENT'])))
				$this->Smarty->compile_dir = $config['compile']['mobile'];
			else
				$this->Smarty->compile_dir = $config['compile']['dir'];
		}

		$this->Smarty->config_dir = $config['main']['themes']."configs/";
		$this->Smarty->cache_dir = $config['main']['themes']."cache/";
//		$this->Smarty->caching = TRUE;
//		$this->Smarty->assign("app_name", $App["name"]);

		$this->Smarty->assign("relativePath", $this->relativePath());
	}

	function register_function($name, $impl)
	{
		return $this->Smarty->register_function($name, $impl);
	}

	function assign($tpl_var, $value = null)
	{
		return $this->Smarty->assign($tpl_var, $value);
	}

	function Show($resource_name, $resource_second = null, $resource_third = null, $cache_id = null, $compile_id = null)
	{
		global $config;

		$theThemes = $this->getMainThemes();
		$theURL = $config['base']['url'];
		$this->template_dir = $theThemes;
		$this->Smarty->template_dir	= $this->template_dir;

		//Parsing HTML
		$Default = TRUE;
		if (($resource_third!="") OR ($resource_third!=null))
		{
			if (file_exists($this->template_dir.$resource_third))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_third, $cache_id, $compile_id);
			}
		}
		
		if ((($resource_second!="") OR ($resource_second!=null)) AND ($Default==TRUE))
		{
			if (file_exists($this->template_dir.$resource_second))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_second, $cache_id, $compile_id);
			}
		}

		if ($Default==TRUE)
		{
			if (file_exists($this->template_dir.$resource_name))
				$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);
			else
				$Output = $this->Smarty->fetch("404.html", $cache_id, $compile_id);
		}
		
		$Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
		// $Output = preg_replace("#img/#",$theURL.$this->template_dir."img/",$Output);
		// $Output = preg_replace("#images/#",$theURL.$this->template_dir."images/",$Output);
		
		// $Output = preg_replace("#css/#",$theURL.$this->template_dir."css/",$Output);
		// $Output = preg_replace("#stylesheets/#",$theURL.$this->template_dir."stylesheets/",$Output);
		// $Output = preg_replace("#icon/#",$theURL.$this->template_dir."icon/",$Output);
		
		// $Output = preg_replace("#js/#",$theURL.$this->template_dir."js/",$Output);
		// $Output = preg_replace("#javascript/#",$theURL.$this->template_dir."javascript/",$Output);
		
		// $Output = preg_replace("#rs-plugin/#",$theURL.$this->template_dir."rs-plugin/",$Output);
		// $Output = preg_replace("#fonts/#",$theURL.$this->template_dir."fonts/",$Output);
		// $Output = preg_replace("#showbizpro/#",$theURL.$this->template_dir."showbizpro/",$Output);
		// $Output = preg_replace("#font/#",$theURL.$this->template_dir."font/",$Output);
		// $Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
		// $Output = preg_replace("#sass/#",$theURL.$this->template_dir."sass/",$Output);
		
		// $Output = preg_replace("#i~m~g/#","img/",$Output);
		// $Output = preg_replace("#i~m~a~g~e~s/#","images/",$Output);
		// $Output = preg_replace("#c~s~s/#","css/",$Output);
		// $Output = preg_replace("#j~s/#","js/",$Output);

		$Output = preg_replace("#a~s~s~e~t~s/#","assets/",$Output);
		return $Output;
	}

	function ShowMember($resource_name, $resource_second = null, $resource_third = null, $cache_id = null, $compile_id = null)
	{
		global $config;

		$theThemes = $this->getMemberThemes();
		$theURL = $config['base']['url'];

		$this->Smarty->compile_dir	= $config['compile']['member'];
		$this->template_dir = $theThemes;
		$this->Smarty->template_dir	= $this->template_dir;

		//Parsing HTML
		$Default = TRUE;
		if (($resource_third!="") OR ($resource_third!=null))
		{
			if (file_exists($this->template_dir.$resource_third))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_third, $cache_id, $compile_id);
			}
		}

		if ((($resource_second!="") OR ($resource_second!=null)) AND ($Default==TRUE))
		{
			if (file_exists($this->template_dir.$resource_second))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_second, $cache_id, $compile_id);
			}
		}

		if ($Default==TRUE)
		{
			if (file_exists($this->template_dir.$resource_name))
				$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);
			else
				$Output = $this->Smarty->fetch("404.html", $cache_id, $compile_id);
		}

		$Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
		$Output = preg_replace("#sass/#",$theURL.$this->template_dir."sass/",$Output);
		
		return $Output;
	}
	
	function ShowAdmin($resource_name, $resource_second = null, $resource_third = null, $cache_id = null, $compile_id = null)
	{
		global $config;
		$theThemes = $config['admin']['themes'];
		$theURL = $config['base']['url'];

		$this->Smarty->compile_dir	= $config['compile']['admin'];
		$this->template_dir = $theThemes;
		$this->Smarty->template_dir	= $this->template_dir;
		//Parsing HTML
		$Default = TRUE;
		if (($resource_third!="") OR ($resource_third!=null))
		{
			if (file_exists($this->template_dir.$resource_third))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_third, $cache_id, $compile_id);
			}
		}
		
		if ((($resource_second!="") OR ($resource_second!=null)) AND ($Default==TRUE))
		{
			if (file_exists($this->template_dir.$resource_second))
			{
				$Default = FALSE;
				$Output = $this->Smarty->fetch($resource_second, $cache_id, $compile_id);
			}
		}

		if ($Default==TRUE)
		{
			if (file_exists($this->template_dir.$resource_name)){
			   
				$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);}
			else
				$Output = $this->Smarty->fetch("404.html", $cache_id, $compile_id);
		}

		$Output = preg_replace("#vendors/#",$theURL.$this->template_dir."vendors/",$Output);
		$Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
		// $Output = preg_replace("#url/#",$theURL.$config['index']['page'].$config['base']['admin']."/",$Output);

		// $Output = preg_replace("#c~s~s/#","css/",$Output);
		// $Output = preg_replace("#j~s/#","js/",$Output);
		// $Output = preg_replace("#i~m~a~g~e~s/#","images/",$Output);
		
		return $Output;
	}

	function cleanURL($String)
	{
		global $config;
		$theThemes = $config['admin']['themes'];
		$theURL = $config['base']['url'];
		$Output = str_replace(array($theURL,$theThemes),'',$String);

		return $Output;
	}

	function ShowTemplatePlugin($resource_name, $cache_id = null, $compile_id = null)
	{
		//Assign Error Template
		global $config;
		$theThemes = $this->getMainThemes();
		$theURL = $config['base']['url'];
		
		$this->template_dir	= $theThemes;
		$this->Smarty->template_dir = $this->template_dir;

		if (file_exists($this->template_dir.$resource_name))
			$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);
		else
			$Output = "";		
		
		return $Output;
	}

	function ShowPlugin($resource_name, $pluginName, $cache_id = null, $compile_id = null)
	{
		//Assign file template
		global $config;
		
		$theURL = ((defined('_URL')) AND (_URL!=""))?_URL:$config['base']['url'];
		
		$this->template_dir	= "../Plugin/".$pluginName."/themes/";
		$this->Smarty->template_dir = $this->template_dir;
		
		if (file_exists($this->template_dir.$resource_name))
			$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);
		else
			$Output = $this->Smarty->fetch("404.html", $cache_id, $compile_id);
			
		$Output = preg_replace("#images_plugin/#",$theURL."Plugin/".$pluginName."/themes/images_plugin/",$Output);		
		return $Output;
	}

	function ShowError($resource_name, $cache_id = null, $compile_id = null)
	{
		global $config;

		$theThemes = $this->getMainThemes();
		$theURL = $config['base']['url'];
		$this->template_dir = $theThemes;
		$this->Smarty->template_dir	= $this->template_dir;

		if (file_exists($this->template_dir.$resource_name))
			$Output = $this->Smarty->fetch($resource_name, $cache_id, $compile_id);
		else
			$Output = $this->Smarty->fetch("404.html", $cache_id, $compile_id);

		$Output = preg_replace("#vendors/#",$theURL.$this->template_dir."vendors/",$Output);
		$Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
				
		// $Output = preg_replace("#img/#",$theURL.$this->template_dir."img/",$Output);
		// $Output = preg_replace("#images/#",$theURL.$this->template_dir."images/",$Output);
		
		// $Output = preg_replace("#css/#",$theURL.$this->template_dir."css/",$Output);
		// $Output = preg_replace("#stylesheets/#",$theURL.$this->template_dir."stylesheets/",$Output);
		// $Output = preg_replace("#icon/#",$theURL.$this->template_dir."icon/",$Output);
		
		// $Output = preg_replace("#js/#",$theURL.$this->template_dir."js/",$Output);
		// $Output = preg_replace("#javascript/#",$theURL.$this->template_dir."javascript/",$Output);
		
		// $Output = preg_replace("#rs-plugin/#",$theURL.$this->template_dir."rs-plugin/",$Output);
		// $Output = preg_replace("#fonts/#",$theURL.$this->template_dir."fonts/",$Output);
		// $Output = preg_replace("#showbizpro/#",$theURL.$this->template_dir."showbizpro/",$Output);
		// $Output = preg_replace("#font/#",$theURL.$this->template_dir."font/",$Output);
		// $Output = preg_replace("#assets/#",$theURL.$this->template_dir."assets/",$Output);
		// $Output = preg_replace("#sass/#",$theURL.$this->template_dir."sass/",$Output);
		
		// $Output = preg_replace("#i~m~g/#","img/",$Output);
		// $Output = preg_replace("#i~m~a~g~e~s/#","images/",$Output);
		// $Output = preg_replace("#c~s~s/#","css/",$Output);
		// $Output = preg_replace("#j~s/#","js/",$Output);
		
		return $Output;
	}

	function checkTutorImage($fileImage)
	{
		$theThemes = $config['admin']['themes'];
		$tutorFolder = "tutor/";
		$theURL = $config['base']['url'];

		if ($fileImage=="")
			return "";
		else
		{
			$fileImage = preg_replace("# #","_",trim(strtolower($fileImage)));
			if (file_exists($theThemes.$tutorFolder.$fileImage))
			{
				return $theURL.$theThemes.$tutorFolder.$fileImage;
			}
			else
				return "";
		}
	}

	function checkURL($URL)
	{
		global $config;
		$vURL = $config['base']['url'];
		return $vURL;
	}

	function relativePath()
	{
		$_temp = explode("/",$_SERVER['SCRIPT_NAME']);
		$u = count($_temp)-1;
		//echo $_temp[$u];
		$Temp = explode($_temp[$u], $_SERVER['SCRIPT_NAME']);
		return $Temp[0];
	}

	function getMemberThemes()
	{
		global $config;
		if ($_SESSION['_fullver']=="yes")
			return $config['member']['themes'];
		else
		{
			if ($this->is_mobile(strtolower($_SERVER['HTTP_USER_AGENT'])))
				return $config['member']['mobile'];
			else
				return $config['member']['themes'];
		}
	}

	function getMainThemes()
	{
		global $config;
		if ($_SESSION['_fullver']=="yes")
			return $config['main']['themes'];
		else
		{
			if ($this->is_mobile(strtolower($_SERVER['HTTP_USER_AGENT'])))
				return $config['mobile']['themes'];
			else
				return $config['main']['themes'];
		}
	}
	
	function actualURL()
	{
		$Http_ = (isset($_SERVER['HTTPS']))?"https":"http";
		return $Http_."://".$_SERVER[HTTP_HOST];
	}

  	function is_mobile($useragent){
    	if(strpos($useragent, 'mobile') !== false || strpos($useragent, 'android') !== false){
      		return true;
   		}
		else if(preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/',$useragent)){
      		return true;
    	}else if(preg_match('/(bolt\/[0-9]{1}\.[0-9]{3})|nexian(\s|\-)?nx|(e|k)touch|micromax|obigo|kddi\-|;foma;|netfront/',$useragent)){
      		return true;
    	}else if(preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/',substr($useragent,0,4))){
      	return true;
    	}	
		return false;
  	}

	function showMessage($Status, $Message)
	{
		switch($Status)
		{
			case "error":
				return "<div id=\"alert-message\" class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">".$Message."<button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-label=\"Close\"><span class=\"font-weight-light\" aria-hidden=\"true\">×</span></button></div>";
			break;
			case "success":
				return "<div id=\"alert-message\" class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">".$Message."<button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-label=\"Close\"><span class=\"font-weight-light\" aria-hidden=\"true\">×</span></button></div>";
			break;
			default:
				return "";
			break;
		}		
	}
	
	function reportMessage($Status, $Message)
	{
		switch($Status)
		{
			case "error":
				$this->assign("reportMessage", $this->showMessage($Status, $Message));
			break;
			case "success":
				$this->assign("reportMessage", $this->showMessage($Status, $Message));			
			break;
			default:
				$this->assign("reportMessage", '');
			break;
		}
	}

	function no_value($vString)
	{
		if ($vString)
			return $vString;
		else
			return "----";
	}

	//API
	function showAPI($Source)
	{
		//Assign file template
		header('Access-Control-Allow-Origin: *');
		return json_encode($Source);
	}
	
	function assignAPI($variable, $value)
	{
		return array($variable => $value);
	}
	
	function dataMerge($variable, $value)
	{
		if (is_array($this->Dump))
			$this->Dump = array_merge($this->Dump,array($variable => $value));
		else
			$this->Dump = array($variable => $value);
		
		return $this->Dump;
	}
	
	function reportAPI($Report)
	{
		return $this->Show(array('reportMessage' => $Report));
	}
	
}
?>