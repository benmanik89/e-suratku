<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

include 'Template.class.php';
include 'Pile.class.php';
include 'Db.class.php';

class Core
{
	//Daftarin Class Core
	var $Template, $Pile, $Db, $Config, $URL, $URLImages;
	function __construct()
	{
		global $config;
		$this->Template = new Template;
		$this->Pile = new Pile;
		$this->Db = new Db;
		$this->Config = $config;
		
		$baseURL = $this->Config['base']['url'];
		$mainURL = $this->Config['main']['url'];
		$fileURL = $this->Config['file']['url'];

		$this->Template->assign("base_url", $baseURL);
		$this->Template->assign("index_page", $this->Config['index']['page']);
		$this->Template->assign("web_url", $baseURL.$this->Config['index']['page']);
		$this->Template->assign("base_admin", $this->Config['base']['admin']);
		$this->Template->assign("base_api", $this->Config['base']['api']);
		$this->Template->assign("admin_url", $baseURL.$this->Config['index']['page'].$this->Config['base']['admin']."/");
		$this->Template->assign("api_url", $baseURL.$this->Config['index']['page'].$this->Config['base']['api']."/");
	}
	
	//URI - Location on URL
	function uri($where=0)
	{
		global $_2ONC;
		$Temp = $_2ONC;
		if (array_key_exists($where, $Temp))
			return $Temp[$where];
		else
			return NULL;
	}
	
	//Fungsi untuk load class tambahan
	//Load secara General
	function load($class, $p1="_UNDEF_", $p2="_UNDEF_", $p3="_UNDEF_", $p4="_UNDEF_")
	{
		$pathClass = "../";
		$classExplode = explode(".", $class);
		$lenClass = count($classExplode);
		
		//Check apakah file clas benar-benar tersedia
		if (file_exists($pathClass.$classExplode[0]."/".$classExplode[1].".class.php"))
		{
			for($i = 0; $i < ($lenClass - 1); $i++)
			{
			  $pathClass .= $classExplode[$i] . "/";
			}
			
			$className = $classExplode[$lenClass - 1];
			if (!isset($appl['flags'][$className]) || !$appl['flags'][$className])
			{
			  include_once($pathClass . $className . ".class.php");
			  $appl['flags'][$className] = TRUE;
			}
	   
			if ($appl['flags'][$className])
			{
			  if ($p1 == '_UNDEF_' && $p1 != 1)
			  {
				eval('$obj = new ' . $className . ';');
			  }
			  else
			  {
				$input = array($p1, $p2, $p3, $p4);
				$i = 1;
				$code = '$obj = new ' . $className . '(';
				while (list($x,$test) = each($input))
				{
				  if (($test == '_UNDEF_' && $test != 1 ) || $i == 5)
				  {
					break;
				  }
				  else
				  {
					$code .= '$p' . $i . ',';
				  }
				  $i++;
				}
				$code = substr($code,0,-1) . ');';
				eval($code);
			  }
			}
			return $obj;
		}
		else
			return false;
	}

	//Fungsi untuk load Plugin
	function LoadPlugin($class, $p1="_UNDEF_", $p2="_UNDEF_", $p3="_UNDEF_", $p4="_UNDEF_")
	{
		$pathClass = "../Plugin/".$class;
		//Check apakah file clas benar-benar tersedia
		if (file_exists($pathClass."/".$class.".class.php"))
		{			
			$className = $class;
			if (!isset($appl['flags'][$className]) || !$appl['flags'][$className])
			{
			  include_once($pathClass ."/". $className . ".class.php");
			  $appl['flags'][$className] = TRUE;
			}
	   
			if ($appl['flags'][$className])
			{
			  if ($p1 == '_UNDEF_' && $p1 != 1)
			  {
				eval('$this->Plugin->'.$class.' = new ' . $className . ';');
			  }
			  else
			  {
				$input = $p1;
				$i = 1;
				$code = '$this->Plugin->'.$class.' = new ' . $className . '(';
				while (list($x,$test) = each($input))
				{
					if ($i!=0)
					{
						  if (($test == '_UNDEF_' && $test != 1 ) || $i == 5)
						  {
							break;
						  }
						  else
						  {
							$code .= '$p' . $i . ',';
						  }
					}
				  $i++;
				}
				$code = substr($code,0,-1) . ');';
				return eval($code);
			  }
			}
			return $obj;
		}
		else
			return "";
	}

	//Fungsi untuk load Module
	function LoadModule($class, $p1="_UNDEF_", $p2="_UNDEF_", $p3="_UNDEF_", $p4="_UNDEF_")
	{
		$pathClass = "../Module";
		//Check apakah file clas benar-benar tersedia
		if (file_exists($pathClass."/".$class.".class.php"))
		{			
			$className = $class;
			if (!isset($appl['flags'][$className]) || !$appl['flags'][$className])
			{
			  include_once($pathClass ."/". $className . ".class.php");
			  $appl['flags'][$className] = TRUE;
			}
	   
			if ($appl['flags'][$className])
			{
			  if ($p1 == '_UNDEF_' && $p1 != 1)
			  {
			  	eval('$this->Module->' .$class. ' = new ' . $className . ';');
			  }
			  else
			  {
				$input = array($p1, $p2, $p3, $p4);
				$i = 1;
				$code = '$this->Module->'.$class.' = new ' . $className . '(';
				while (list($x,$test) = each($input))
				{
				  if (($test == '_UNDEF_' && $test != 1 ) || $i == 5)
				  {
					break;
				  }
				  else
				  {
					$code .= '$p' . $i . ',';
				  }
				  $i++;
				}
				$code = substr($code,0,-1) . ');';
				eval($code);
			  }
			}
		}
		else
			return false;
	}

	//Fungsi untuk load Application
	function LoadApplication($class, $p1="_UNDEF_", $p2="_UNDEF_", $p3="_UNDEF_", $p4="_UNDEF_")
	{
		$pathClass = "../Application";
		//Check apakah file clas benar-benar tersedia
		if (file_exists($pathClass."/".$class.".class.php"))
		{			
			$className = $class;
			if (!isset($appl['flags'][$className]) || !$appl['flags'][$className])
			{
			  include_once($pathClass ."/". $className . ".class.php");
			  $appl['flags'][$className] = TRUE;
			}
	   
			if ($appl['flags'][$className])
			{
			  if ($p1 == '_UNDEF_' && $p1 != 1)
			  {
				eval('$this->Application->' .$class. ' = new ' . $className . ';');
			  }
			  else
			  {
				$input = array($p1, $p2, $p3, $p4);
				$i = 1;
				$code = '$this->Application->'.$class.' = new ' . $className . '(';
				while (list($x,$test) = each($input))
				{
				  if (($test == '_UNDEF_' && $test != 1 ) || $i == 5)
				  {
					break;
				  }
				  else
				  {
					$code .= '$p' . $i . ',';
				  }
				  $i++;
				}
				$code = substr($code,0,-1) . ');';
				eval($code);
			  }
			}
		}
		else
			return false;
	}

	//Fungsi untuk load Language
	function LoadLanguage($class, $p1="_UNDEF_", $p2="_UNDEF_", $p3="_UNDEF_", $p4="_UNDEF_")
	{
		$pathClass = "../Language";
		//Check apakah file clas benar-benar tersedia
		if (file_exists($pathClass."/".$class.".class.php"))
		{			
			$className = $class;
			if (!isset($appl['flags'][$className]) || !$appl['flags'][$className])
			{
			  include_once($pathClass ."/". $className . ".class.php");
			  $appl['flags'][$className] = TRUE;
			}
	   
			if ($appl['flags'][$className])
			{
			  if ($p1 == '_UNDEF_' && $p1 != 1)
			  {
				eval('$this->Language->' .$class. ' = new ' . $className . ';');
			  }
			  else
			  {
				$input = array($p1, $p2, $p3, $p4);
				$i = 1;
				$code = '$this->Language->'.$class.' = new ' . $className . '(';
				while (list($x,$test) = each($input))
				{
				  if (($test == '_UNDEF_' && $test != 1 ) || $i == 5)
				  {
					break;
				  }
				  else
				  {
					$code .= '$p' . $i . ',';
				  }
				  $i++;
				}
				$code = substr($code,0,-1) . ');';
				eval($code);
			  }
			}
		}
		else
			return false;
	}

	//Error Result
	function Error($errNumber)
	{
		switch($errNumber)
		{
			case "404":
				echo $this->Template->ShowAdmin("404.html");
			break;
		}
		die();
	}
	
	function getURL()
	{
		return $this->Config['base']['url'].$this->Config['index']['page'];
	}
	
	//Get Google Refferal
	function getGoogleSearch()
	{
		// The referrer
		$referrer = $_SERVER['HTTP_REFERER'];

		// Parse the URL into an array
		$parsed = parse_url( $referrer, PHP_URL_QUERY );

		// Parse the query string into an array
		parse_str( $parsed, $query );

		// Output the result
		return trim($query['q']);
	}

	function parseRequestHeaders() {
		$headers = array();
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') { continue; }
				//$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
				$header = str_replace(' ', '-', str_replace('_', ' ', strtolower(substr($key, 5))));
				$headers[$header] = $value;
			}
		return $headers;
	}
	
	function getBody()
	{
		$Temp = file_get_contents('php://input');
		return json_decode($Temp, TRUE);
	}
	
	function API_Access()
	{
		$headers = $this->parseRequestHeaders();
		$publicKey = ($_GET['publickey'])?$_GET['publickey']:$headers['publickey'];
		$privateKey = ($_GET['privatekey'])?$_GET['privatekey']:$headers['privatekey'];
		$Check = $this->Db->sql_query_array("SELECT * FROM cpapi WHERE vAPI='".$publicKey."' AND vKey='".$privateKey."'");
		if ($Check['id']=="")
			return false;
		else
			return true;
	}
	
}
?>