<?php
// $readOpt = $this->Db->sql_query_array("SELECT id FROM cppagecategory WHERE vCategory='OPTIONAL'");
// $baca = $this->Db->sql_query("SELECT * FROM cppage WHERE idCategory='".$readOpt['id']."'");
// while ($Baca = $this->Db->sql_array($baca))
// {
// 	$ModuleSetting = $this->Module->Options->getContentSetting($Baca['id'], "page");
// 	$PictureSetting = $this->Module->Options->getPictureSetting($Baca['id'], "page");

// 	$listContentSetting = $this->Module->Options->listContentSetting($Baca['id'], "page");
// 	$OPTIONAL[$Baca['vPageName']] = array_merge($Baca,array(
// 		'module' => $ModuleSetting, 
// 		'modulepicture' => $PictureSetting,
// 		'listsetting' => $listContentSetting
// 	));
// }

// $this->Template->assign("TITLE", $OPTIONAL['TITLE']['tContent']);	
// $this->Template->assign("COPYRIGHT", $OPTIONAL['COPYRIGHT']['tContent']);

// $this->Template->assign("METATITLE", $OPTIONAL['METATITLE']['tContent']);	
// $this->Template->assign("METADESC", $OPTIONAL['METADESC']['tContent']);	
// $this->Template->assign("METAKEYWORD", $OPTIONAL['METAKEYWORD']['tContent']);
// $this->Template->assign("FAVICON", $OPTIONAL['FAVICON']['lbPicture']);
// $this->Template->assign("LOGO", $OPTIONAL['LOGO']['lbPicture']);

// $this->Template->assign("OPTIONAL", $OPTIONAL);

// $this->Template->assign("pageDir", $this->Config['page']['dir']);
// $this->Template->assign("FOLDER", $this->Config['page']['dir']);

function PLUGIN($param)
{
	global $Core;
	extract($param);
	if ($NAME!="")
	{
		$Core->LoadPlugin($NAME);
		$Core->Plugin->$NAME->Load($VAR1, $VAR2, $VAR3, $VAR4, $VAR5, $VAR6);
	}
}
$this->Template->register_function("PLUGIN", "PLUGIN");

if ($_GET['fullversion']=="yes")
{
	//setcookie("_fullver","yes",time()+3600);
	$_SESSION['_fullver'] = "yes";
	echo "<script>location.href='".$this->getURL()."';</script>";
	die();
}

if ($_GET['fullversion']=="mobile")
{
	//setcookie("_fullver","",time()-3600);
	$_SESSION['_fullver'] = "";
	unset($_SESSION['_fullver']);
	echo "<script>location.href='".$this->getURL()."';</script>";
	die();
}

if ($this->Template->is_mobile(strtolower($_SERVER['HTTP_USER_AGENT'])))
	$this->Template->assign("MobileVersion", "yes");

?>