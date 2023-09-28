<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

require 'Thumbnail.class.php';
class Pile extends thumbnail_images
{
	var $fileSource,
	$fileName,
	$fileTmpName,
	$fileType,
	$fileSize,
	$fileExt,
	$fileNewName,
	$fileDestination,
	$Config;

	var $setFileType, $setFileMax;
	
	function __construct()
	{
		//Constructor right here
		global $config;
		$this->Config = $config;
		
		if ($this->fileDestination == "")
			$this->fileDestination = $this->Config['file']['path'];		
	}

	function PileRecord()
	{
		$this->fileName	= $this->fileSource['name'];
		$this->fileType = $this->fileSource['type'];
		$this->fileSize = $this->fileSource['size'];
		$this->fileTmpName = $this->fileSource['tmp_name'];

		$Ext=explode(".",strrev($this->fileSource['name']));
		$this->fileExt = strrev($Ext[0]);
	}

	//function to check however the file name is exists
	function validateFile()
	{
		if ($this->fileTmpName!="") return true;
		else return false;
	}

	function readFile($filename)
	{
		if ($handle = fopen($filename, 'r')) {
			$Temp = fread($handle,filesize($filename));
			fclose($handle);
			return $Temp;
		}
		else
			return false;
	}

	function writeFile($filename,$somecontent)
	{
		// Let's make sure the file exists and is writable first.
		if (is_writable($filename)) {

		   // In our example we're opening $filename in append mode.
		   // The file pointer is at the bottom of the file hence
		   // that's where $somecontent will go when we fwrite() it.
		   if (!$handle = fopen($filename, 'w')) {
				return "Cannot open file ($filename)<br>";
				exit;
		   }

		   // Write $somecontent to our opened file.
		   if (!fputs($handle, $somecontent)) {
			   return "<font color=red><b>Cannot write to file ($filename)</b></font>";
			   exit;
		   }

		   return "<font color=blue><b>Success! wrote (somecontent) to file ($filename)</b></font>";

		   fclose($handle);

		} else {
		   return "<font color=red><b>The file $filename is not writable</b></font>";
		}

	}

	function breakPage($filename,$delimiter)
	{
		if ($this->validateOldFile($filename)){
			$String=implode('',file($filename));
			$string=explode($delimiter, $String);
			return $string;
		}
		else
			return false;
	}

	function breakPageNoDelimiter($filename)
	{
		if ($this->validateOldFile($filename)){
			$String=implode('',file($filename));
			return $String;
		}
		else
			return false;
	}

	//function to show the file properties
	function showFileProperties()
	{
		if ($this->validateFile()){

			$Report = "<table cellpadding=2 cellspacing=2 border=1 width=50%>";
			$Report .= "<tr><td width=30%><b>File Name</b></td><td>".$this->fileName."</td></tr>";
			$Report .= "<tr><td><b>File Type</b></td><td>".$this->fileType."</td></tr>";
			$Report .= "<tr><td><b>File Size</b></td><td>".$this->fileSize." bytes</td></tr>";
			$Report .= "</table><br>";

			return $Report;
		}
		else
			return "Ops! There is no avaiable file";
	}

	//function to validate the file type "image/jpeg,image/gif,plain/text"
	function validateFileType($Type)
	{
		$Stat=false;
		if ($this->validateFile())
		{
			$Temp = explode(",",$Type);
			for ($i=0; $i<count($Temp); $i++)
			{
				if ($this->fileType==$Temp[$i]){
					$Stat=true;
					break;
				}
				$Stat=false;
			}
		}
		else $Stat = false;

		return $Stat;
	}

	//function to validate the file size
	function validateFileSize($Size)
	{
		if ($this->validateFile())
		{
			if ($this->fileSize > $Size) return false;
			else return true;
		}
		else return false;
	}

	//function to check if there is the old file has the same name with new file
	function validateOldFile($FileName)
	{
		if ($FileName=="")
			return false;
		else
			return @file_exists($this->fileDestination.$FileName);
	}

	//function to delete the old file if there a same name with the new file
	function deleteOldFile($FileName)
	{
		if ($this->validateOldFile($FileName))
		{
			if (unlink($this->fileDestination.$FileName)) return true;
			else return false;
		}
		else return false;

	}

	//funtion to copy the new file into file destination without renamed it
	function copyNewFile()
	{
		if (!$this->validateOldFile($this->fileName))
		{
			if (move_uploaded_file($this->fileTmpName,$this->fileDestination.$this->fileName)) return true;
			else return false;
		}
		else return false;
	}

	//function to copy the new file into it's destination with renamed it
	function copyRenameNewFile($NewFile)
	{
		if (!$this->validateOldFile($NewFile))
		{
			if (move_uploaded_file($this->fileTmpName,$this->fileDestination.$NewFile))
			{
				$this->fileNewName = $NewFile;
				return true;
			}
			else return false;
		}
		else return false;
	}

	function readDirectory($dir,$extFile)
	{
		//tampilin file
		$dirW=opendir($dir);
		$d=0;
		while ($file=readdir($dirW)){
			//array
			$Temp=explode(".",$file);
			if (($Temp[1]==$extFile) AND ($file!="index.htm") AND ($file!="index.html"))
				$file_[$d++]=$file;
			//$d++;
			//-----
		}
		if ($file_){
			sort($file_);
			reset($file_);
		}
			closedir($dirW);
			return $file_;
	}

	function readDir($dir)
	{
		//tampilin file
		$dirW=opendir($dir);
		$d=0;
		while ($file=readdir($dirW)){
			//array
			if (($file!="index.html") AND ($file!="index.htm") AND ($file!=".") AND ($file!=".."))
				$file_[$d++]=$file;
			//$d++;
			//-----
		}
			if ($file_){
				sort($file_);
				reset($file_);
			}
			closedir($dirW);
			return $file_;
	}

	function readTemplateDir($dir, $pattern="")
	{
		//tampilin file
		$dirW=opendir($dir);
		$d=0;
		while ($file=readdir($dirW)){
			//array
			if ($pattern=="")
				$file_[$d++]=$file;
			else
			{
				if (eregi($pattern, $file))
					$file_[$d++]=$file;
			}
			//$d++;
			//-----
		}
		
			if ($file_){
				sort($file_);
				reset($file_);
			}
			closedir($dirW);
			return $file_;
	}

	//Simpan Files
	function saveFile($FileSource,$NewFileName_="",$original=TRUE,$thumb=FALSE) //function to check and save images
	{
		$fileUpload=false; //set fileUpload to false
			
		$thisFileType = ($this->setFileType=="")?$this->Config['doc']['filetype']:$this->setFileType;
		$thisFileMax = ($this->setFileMax=="")?($this->Config['file']['max_file_backup']*1024):$this->setFileMax;
		
		//set the temporary directory
		$this->fileSource=$FileSource;
		$this->PileRecord();
		
		$NewFileName = ($NewFileName_=="")?preg_replace("# #","",strtolower($this->fileName)):$NewFileName_;
				
		//set the images file type
		if ($this->validateFileType($thisFileType)){
			if ($this->validateFileSize($thisFileMax)){ //set the maximum file size
				if ($thumb)
				{
					$this->PathImgOld = $this->fileTmpName;
					$this->PathImgNew = $this->fileDestination."thumb_".$NewFileName.".".$this->fileExt;
					$this->NewWidth = $this->Config['photo']['thumbnail'][0];
					$this->NewHeight = $this->Config['photo']['thumbnail'][1];
					$this->create_thumbnail_images();
				}
				
				if ($original)
				{
					$this->copyRenameNewFile($NewFileName.".".$this->fileExt);
				}
				
				$fileUpload=true;
			} 
			
		}
		
			
		//compare if there is an image or not
		$vPile = ($fileUpload)?$NewFileName.".".$this->fileExt:NULL;
		return $vPile;
	}

	function readMemberDir($Directory)
	{
		$dir=opendir($Directory);
			$i=0; 
			while ($file=readdir($dir)){
			
			clearstatcache();
			
			if (!(($file==".") OR ($file=="..") OR ($file=="index.php") OR ($file=="index.html") OR ($file=="index.htm"))){
					$fileDesc = explode(".",$file);
					$dataFile[$i] = array (	"fileName" => $file,
											"fileType" => $fileDesc[1],
											"icon" => (is_dir($Directory.$file)?"dir_icon.gif":"file_icon.gif"),
											"type" => (is_dir($Directory.$file)?"dir":"file"),
											);
					//echo $dataFile[$i]['fileName'];
					$i++;
				}
			}
			closedir($dir);
			return $dataFile;
	}

	//Simpan Files
	function simpanFile($FileSource, $NewFileName_ = "") //function to check and save images
	{
		$fileUpload = false; //set fileUpload to false

		//set the temporary directory
		$this->fileSource = $FileSource;
		$this->PileRecord();

		if ($this->fileName != "") {
			if ($NewFileName_ == "") {
				$this->copyNewFile();
				//$NewFileName = preg_replace('/\s+/','',strtolower($this->fileName));
				$NewFileName = $this->fileName;
			} else {
				$NewFileName = preg_replace('/\s+/', '', strtolower($NewFileName_)) . "." . $this->fileExt;
				$this->copyRenameNewFile($NewFileName);
			}

			return $NewFileName;
		} else
			return NULL;
	}

	//Simpan Images
	function simpanImage($ImageSource,$NewFile,$thumb=FALSE) //function to check and save images
	{
		$fileUpload=false; //set fileUpload to false
			
		$thisFileType = ($this->setFileType=="")?$this->Config['images']['filetype']:$this->setFileType;
		$thisFileMax = ($this->setFileMax=="")?$this->Config['file']['max_icon_size']:$this->setFileMax;
		
		//set the temporary directory
		$this->fileSource=$ImageSource;
		$this->PileRecord();
		$data = $this->validateFileType($thisFileType);
		return $data;
		//set the images file type
		if ($this->validateFileType($thisFileType)){
			if ($this->validateFileSize($thisFileMax)){ //set the maximum file size
				if ($thumb)
				{
					$this->PathImgOld = $this->fileTmpName;
					$this->PathImgNew = $this->fileDestination.$NewFile.".".$this->fileExt;
					$this->NewWidth = $this->Config['icon']['thumbnail'][0];
					$this->NewHeight = $this->Config['icon']['thumbnail'][1];
					$this->create_thumbnail_images();
				}
				else
				{
					$this->copyRenameNewFile($NewFile.".".$this->fileExt);
				}
				
				$fileUpload=true;
			} 
		} 
		//compare if there is an image or not
		$vPile = ($fileUpload)?$NewFile.".".$this->fileExt:NULL;
		return $vPile;
	}
	
	//Upload Doc
	function uploadDoc($fileSource, $NewFile) //function to check and save images
	{
		$fileUpload=false; //set fileUpload to false
			
		$thisFileType = ($this->setFileType=="")?$this->Config['doc']['filetype']:$this->setFileType;
		$thisFileMax = ($this->setFileMax=="")?$this->Config['file']['max_file_size']:$this->setFileMax;

		//set the temporary directory
		$this->fileSource=$fileSource;
		$this->PileRecord();
				
		//set the images file type
		if ($this->validateFileType($thisFileType)){
			if ($this->validateFileSize($thisFileMax)){ //set the maximum file size
				$this->copyRenameNewFile($NewFile.".".$this->fileExt);
				$fileUpload=true;
			}
		}
		//compare if there is an image or not
		$vPile = ($fileUpload)?$NewFile.".".$this->fileExt:NULL;
		return $vPile;
	}

}
?>