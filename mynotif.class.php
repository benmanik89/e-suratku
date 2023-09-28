<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class mynotif extends Core
{
	var $Submit, $Action, $Do, $Id, $idStatus, $DetailAdmin;
	public function __construct()
	{
		parent::__construct();
		
		//Load General Process
		include '../inc/general_admin.php';

		$this->LoadModule("Notif");
		$this->LoadModule("Auth");
		$this->Template->assign("Signature", "master");
		ob_clean();
	}
		
	function main()
	{
		echo $this->Template->ShowAdmin("notif/notif_index.html");
	}

	function loaddata()
	{
		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length'];
		
		$columnIndex = $_POST['order'][0]['column'];
		$columnName = $_POST['columns'][$columnIndex]['data'];
		
		$columnSortOrder = $_POST['order'][0]['dir'];
		$searchValue = $_POST['search']['value'];
		
		//Search
		$searchQuery = "";
		if ($searchValue != '')
		{
			$searchQuery = " AND (tNotif like '%".$searchValue."%')";
		}
		
		//Total Records without Filtering
		$records = $this->Db->sql_query_array("select count(*) as total from cpnotif where user_id='".$this->DetailAdmin['id']."'");
		$totalRecords = $records['total'];
		
		//Total Record with filtering
		$records = $this->Db->sql_query_array("select count(*) as total from cpnotif where id!='0' and user_id='".$this->DetailAdmin['id']."'".$searchQuery);
		$totalRecordsWithFilter = $records['total'];
		
		//Fetch Records
		$orderBy =" order by dTanggal desc";
		$limitBy = ($row=="")?"":" limit ".$row.",".$rowperpage;
		
		$sqlQuery = "select * from cpnotif where id!='0' and user_id='".$this->DetailAdmin['id']."'".$searchQuery.$orderBy.$limitBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$navButton = "<a href=\"javascript:deletedata(".$row['id'].")\"><i class='fas fa-trash-alt'></i></a>";

			$detailMember = $this->Module->Auth->detailAdmin($row['user_id']);
			$dTanggal = date("d F Y - H:i:s", strtotime($row['dTanggal']));

			$data[] = array(
				'user_id' => $this->Template->no_value($detailMember['vName']),
				'tNotif' => "<a href=\"".$this->Config['admin']['url'].$row['vPage']."/detail?id=".$row['idPage']."\">".$this->Template->no_value($row['tNotif'])."</a>",
				'vPage' => $this->Template->no_value($row['vPage']),
				'idPage' => $this->Template->no_value($row['idPage']),
				'dTanggal' => $this->Template->no_value($dTanggal),
				'iRead' => (($row['iRead']=="1")?"<span class=\"badge badge-secondary\">Read</span>":"<span class=\"badge badge-warning\"><i>Unread</i></span>"),
				'navButton' => $navButton,
			);
		}
		
		//Response
		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecordsWithFilter,
			"iTotalDisplayRecords" => $totalRecords,
			"aaData" => (($data)?$data:array())
		);
		
		echo json_encode($response);
	}

	function delete()
	{
		if ($this->Id!="")
		{
			if ($this->Module->Notif->delete($this->Id))
			{
				$Return = array('status' => 'success',
				'message' => $this->Template->showMessage('success', 'Data notif telah di hapus'), 
				'data' => ''
				);
				// $Return = array('status' => 'success',
				// 'message' => 'Data telah di hapus', 
				// 'data' => ''
				// );
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => $this->Template->showMessage('error', 'Ops! ID notif tidak valid'), 
			'data' => ''
			);				
		}

		echo json_encode($Return);
	}

	function deleteAll()
	{
		$listall  = $this->Module->Notif->listAll($this->DetailAdmin['id']);
			for($i=0;$i<count($listall);$i++) {
				if ($this->Module->Notif->delete($listall[$i]['Item']['id']))
				{
					$Return = array(
						'status' => 'success',
						'message' => $this->Template->showMessage('success', 'Semua data notif telah dihapus'), 
						'data' => ''
					);
				} else {
					$Return = array(
					'status' => 'error',
					'message' => $this->Template->showMessage('error', 'Ops! Terjadi kesalahan'), 
					'data' => ''
					);	
					break;
				}

			}
			echo json_encode($Return);
	}

}
