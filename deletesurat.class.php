<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class deletesurat extends Core
{
	var $Submit, $Action, $Do, $Id, $idStatus, $DetailAdmin, $getTahun, $dirTicket;
	public function __construct()
	{
		parent::__construct();
		
		//Load General Process
		include '../inc/general_admin.php';

		$this->dirTicket = $this->Config['upload']['dir'];
		$this->Pile->fileDestination = $this->dirTicket;
		$this->Template->assign("dirTicket", $this->dirTicket);

		$this->LoadModule("Ticket");

		$this->Template->assign("Signature", "master");

		// $this->Module->Auth->verifyAdmin(array('superadmin'), "", $this->DetailAdmin);
		if ($this->DetailAdmin['jabatan_slug'] != 'superadmin'){
			echo $this->Template->ShowAdmin("404.html");
			die();
		}
		ob_clean();
	}
		
	function main()
	{
		echo $this->Template->ShowAdmin("surat/surat_index.html");
	}

	function delete()
	{
		
		$getTanggal = $_GET['tanggal'];
		$_getTanggal = explode("to", $getTanggal);
		$fromDate = trim($_getTanggal[0]);
		$fromDate = ($fromDate)?$fromDate:date("Y-m-d");

		$toDate = trim($_getTanggal[1]);
		$toDate = ($toDate)?$toDate:date("Y-m-d");
		if ($getTanggal!="")
		{
			$_DATE = " AND (created_date BETWEEN '".$fromDate." 00:00:00' AND '".$toDate." 23:59:59')";
			$listall  = $this->Module->Ticket->listByDate($_DATE);
			for($i=0;$i<count($listall);$i++) {
				$myfile = json_decode($listall[$i]['Item']['attacment'], true);
				for ($j=0;$j<count($myfile);$j++) {
					$this->Pile->deleteOldFile($myfile[$j]['id']);
				}
				$this->Module->Ticket->deleteRevisi($listall[$i]['Item']['id']);
				if ($this->Module->Ticket->delete($listall[$i]['Item']['id']))
				{
					$Return = array('status' => 'success',
					'message' => 'Data surat berhasil di hapus', 
					'data' => ''
					);
				} else {
					$Return = array('status' => 'error',
					'message' => 'Ops! Ada eror pada database', 
					'data' => ''
					);
					break;
				}

			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! Date surat tidak valid'), 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

}

?>