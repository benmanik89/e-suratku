<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class
	//PLEASE DO NOT MODIFY THIS CODE!!!!!//
	//Read Optional Config
	function readConfig($Directory)
	{
		$dir=opendir($Directory);
			$i=0; 
			while ($file=readdir($dir)){
			clearstatcache();
			if (!(($file==".") OR ($file=="..") OR ($file=="index.php") OR ($file=="core.php") OR ($file=="config-new.php") OR ($file=="index.html") OR ($file=="index.htm"))){
					$dataFile[$i] = array ("fileName" => $file );
					$i++;
				}
			}
			closedir($dir);
			return $dataFile;
	}
	//---------------------------------
?>