<?php
	//Session Check
	$this->LoadModule("Signin");
	$this->LoadModule("Session");
	$this->Module->Session->refresh();
	//$session_Signature = md5($_SERVER['HTTP_USER_AGENT']."-".$_SERVER['REMOTE_ADDR']);
	$session_Signature = session_id();
	$getSession = $this->Module->Session->getSessionHistory($session_Signature);
	if ($getSession['idMember']=="")
	{	
		echo "<script>location.href='".$this->getURL()."login'</script>";
		die("Ops!! Uhh ohh.. You have to <a href=\"".$this->getURL()."login\">login</a> before manage you account!!");
	}
	else
	{
		$this->Submit=($_POST['submit'])?$_POST['submit']:$_GET['submit'];
		$this->Action=($_POST['action'])?$_POST['action']:$_GET['action'];
		
		$this->Id=($_POST['id'])?$_POST['id']:$_GET['id'];
		$this->Template->assign("Id", $this->Id);
		
		$this->idMember = $getSession['idMember'];
		$this->Template->assign("idMember", $this->idMember);

		$this->detailMember = $this->Module->Koperasi->detail($this->idMember);
		$this->Template->assign("detailMember", $this->detailMember);
		$this->Module->Session->updateSession($session_Signature,$this->idMember);
		
		//Last Login
		$this->Template->assign("lastLogin", $this->Module->Signin->lastLogin($this->idMember));
	}
	//-----------
?>