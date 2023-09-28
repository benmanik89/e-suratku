<?php  
if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class mysurat extends Core
{
	var $Submit, $Action, $Do, $Id, $idStatus, $dirTicket, $DetailAdmin, $DetailAdminDir;
	public function __construct()
	{
		parent::__construct();
		//Load General Process
		include '../inc/general_admin.php';

		$this->LoadModule("Ticket");
		$this->LoadModule("Auth");
		$this->LoadModule("Jenissurat");
		$this->LoadModule("Jabatan");
		$this->LoadModule("Converter");
		
		$this->dirTicket = $this->Config['upload']['dir'];
		$this->Pile->fileDestination = $this->dirTicket;
		$this->Template->assign("dirTicket", $this->dirTicket);
		$this->Template->assign("dirUser", $this->Config['user']['dir']);
		$this->Template->assign("dirTemp", $this->Config['temp']['dir']."revisi_".$this->Id."/");

		$this->Template->assign("Signature", "surat");

		//Auth Check
		//$this->Module->Auth->verifyAdmin("myemployee", "", $this->DetailAdmin);
		ob_clean();
	}
		
	function main()
	{
		echo $this->Template->ShowAdmin("ticket/ticket_index.html");
	}

	function getpage() {	
		$do = ($this->Do == '') ? 'inbox' : $this->Do;
		$this->Template->assign('do', $do);
		switch ($do) {
			default:
				$getDetail = $this->Template->ShowAdmin("ticket/ticket_list.html");
				break;
		}
		if ($do == 'list_all' AND $this->DetailAdmin['jabatan_slug'] != 'superadmin') {
				echo "<script>location.href=\"".$this->Config['admin']['url']."\"</script>";
				die();
		}
		$title = $this->Template->ShowAdmin('ticket/ticket_title.html');

		$json_data = array(
			'title' => $title,
			'detail' => $getDetail
		);

		echo json_encode($json_data);
	}

	function loaddata() {
		$do = $this->Do;
		if ($do == 'sent') $query_author = " AND author='".$this->DetailAdmin['id']."'";
		$getTanggal = $_GET['tanggal'];
		$_getTanggal = explode("to", $getTanggal);
		$fromDate = trim($_getTanggal[0]);
		$fromDate = ($fromDate)?$fromDate:date("Y-m-d");

		$toDate = trim($_getTanggal[1]);
		$toDate = ($toDate)?$toDate:date("Y-m-d");
		
		$_DATE = "";
		if ($getTanggal!="")
		{
			$_DATE = " AND (created_date BETWEEN '".$fromDate." 00:00:00' AND '".$toDate." 23:59:59')";
		}
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
			$searchQuery = " AND (title like '%".$searchValue."%')";
		}
		
		//Total Records without Filtering
		$records = $this->Db->sql_query_array("select count(*) as total from tickets where id!='0'".$query_author.$_DATE);
		$totalRecords = $records['total'];
		
		//Total Record with filtering
		$records = $this->Db->sql_query_array("select count(*) as total from tickets where id!='0'".$query_author.$searchQuery.$_DATE);
		$totalRecordsWithFilter = $records['total'];
		
		//Fetch Records
		$orderBy =" order by created_date desc";
		$limitBy = ($row=="")?"":" limit ".$row.",".$rowperpage;
		
		$sqlQuery = "select * from tickets where id!='0'".$query_author.$searchQuery.$_DATE.$orderBy.$limitBy;
				
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$ticket_id= ($row['ticket_id'] != '0')?$row['ticket_id']:$row['id'];
			$getStatus= $this->Module->Ticket->getcheckdone($ticket_id);
			if ($getStatus['status'] == '1') $checkdone = " <span class=\"badge badge-success\"> <i class=\"fas fa-check-circle\"></i> Selesai</span>"; 
			$petugas = $this->Module->Auth->detailAdmin($row['petugas']);
			$petugasJabatan = $this->Module->Jabatan->detail($petugas['id_jabatan']);
			$navButton = "<a href=\"javascript:deletedata(".$row['id'].")\"><i class='fas fa-trash-alt'></i></a>";
			$Button = "<a  href=\"javascript:previewsurat(".$row['id'].",'".$row['vNoSurat']."')\"><i class='fas fa-eye'></i></a>";

			$start_date = date("d F Y", strtotime($row['start_date']));
			$end_date = date("d F Y", strtotime($row['end_date']));
			if ($row['is_read'] == '1') {
				$iread = "<span class=\"badge badge-secondary\">Read</span>";
				$sread = "";
			} else {
				$iread = "<span class=\"badge badge-danger\">Unread</span>";
				$sread = "<i class=\"fas fa-circle text-info\"></i>";
			}

			if ($row['diteruskan'] != '0') {
				$penerus = $this->Module->Auth->detailAdmin($row['diteruskan']);
				$penerusJabatan = $this->Module->Jabatan->detail($penerus['id_jabatan']);
				$diteruskan = "<br /><i class=\"fas fa-share-alt\"></i> Dishare oleh ". $penerus['vName'] . " (".$penerusJabatan['name'].")";
			}
			if ($row['ticket_id'] != '0') {
				$replysurat = $this->Module->Ticket->detail($row['ticket_id']);
				$reply_string = "<br /><a href=\"".$this->Config['admin']['url']."mysurat/detail?id=".$row['ticket_id']."\" class=\"text-info\"><i class=\"fas fa-reply\"></i> Reply surat ". $replysurat['title'];
			}

			switch($row['status'])
			{
				case "approved":$color = "badge-info"; $status_text="Disetujui";break;
				case "done":$color = "badge-success"; $status_text="Selesai";break;
				case "rejected":$color = "badge-danger"; $status_text="Direvisi";break;
				case "progress":$color = "badge-warning"; $status_text="Diproses";break;
				case "hold":$color = "badge-secondary"; $status_text="Hold";break;
				default:$color = ""; $status_text="";break;
			}
			$status = "<span class=\"badge ".$color."\">".$status_text."</span>";
			$author = $this->Module->Auth->detailAdmin($row['author']);
			$jabatan = $this->Module->Jabatan->detail($author['id_jabatan']);
			$authorSurat = $author['vName']." (".$jabatan['name'].")";
			if (($do == 'inbox') AND ($this->DetailAdmin['id'] == $row['petugas'])) {
				$data[] = array(
					"title" => "<a href=\"".$this->adminURL."mysurat/detail?id=".$row['id']."\">".$row['title']."</a>".$reply_string,
					'author' => $authorSurat . $diteruskan,
					'toSent' => $petugas['vName'],
					"start_date" => $start_date." s/d ".$end_date,
					"status" => $status,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']) . " ".$sread .$checkdone,
					"navButton" => $navButton,
				);
			} else if ($do == 'sent' && $this->DetailAdmin['id'] == $row['author']) {
    			if ($this->DetailAdmin['jabatan_slug'] == 'superadmin' || $this->DetailAdmin['jabatan_slug'] == 'kepala-dinas' || $this->DetailAdmin['jabatan_slug'] == 'kepala-bidang' || $this->DetailAdmin['jabatan_slug'] == 'analis-sistem-informasi') {
        			$data[] = array(
            		"title" => "<a href=\"".$this->adminURL."mysurat/detail?id=".$row['id']."\">".$row['title']."</a>",
					'author' => $author['vName']." (".$jabatan['name'].")",
					'toSent' => $petugas['vName']." (".$petugasJabatan['name'].")",
					"start_date" => $start_date." s/d ".$end_date,
					"status" => $status,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']). " ".  $iread." ". $status . $checkdone,
					"Button" => $Button,
       				 );}
	
					else {
						$data[] = array(
							"title" => "<a href=\"".$this->adminURL."mysurat/detail?id=".$row['id']."\">".$row['title']."</a>",
							'author' => $author['vName']." (".$jabatan['name'].")",
							'toSent' => $petugas['vName']." (".$petugasJabatan['name'].")",
							"start_date" => $start_date." s/d ".$end_date,
							"status" => $status,
							"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']). " ".  $iread." ". $status .$checkdone,
						);}
					} else if ($do == 'list_all' AND $this->DetailAdmin['jabatan_slug']=='superadmin') {
									$data[] = array(
										"title" => "<a href=\"".$this->adminURL."mysurat/detail?id=".$row['id']."\">".$row['title']."</a>",
										"author" => $author['vName']." (".$jabatan['name'].")",
										"toSent" => $petugas['vName']." (".$petugasJabatan['name'].")",
										"start_date" => $start_date." s/d ".$end_date,
										"status" => $status,
										"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']),
										"navButton" => $navButton .$checkdone,
									);
								}
			if ($do == 'inbox')
			$totalRecords = count($data);
			$totalRecordsWithFilter = count($data);
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
			$listReport = $this->Module->Ticket->detail($this->Id);
			$listFile = json_decode($listReport['attacment'], true);
			$query = $this->Db->sql_query_array("SELECT * FROM tickets WHERE (attacment LIKE '%".$listReport['attacment']."%') LIMIT 1");

			if ($query['id']) {
				for ($i=0;$i<count($listFile);$i++)
				{
					$this->Pile->deleteOldFile($listReport[$i]['id']);
				}
			}
			$this->Module->Ticket->deleteRevisi($this->Id);
			if ($this->Module->Ticket->delete($this->Id))
			{
				$Return = array('status' => 'success',
				'message' => 'Data telah di hapus', 
				'data' => ''
				);
			}
		}
		else
		{
			$Return = array('status' => 'error',
			'message' => 'Ops! ID surat tidak valid', 
			'data' => ''
			);			
		}

		echo json_encode($Return);
	}

	function pdf()
	{	
		$do = $this->Do;
		
		if ($do == 'sent') $query_author = " AND author='".$this->DetailAdmin['id']."'";
		$getTanggal = $_GET['tanggal'];
		$_getTanggal = explode("to", $getTanggal);
		$fromDate = trim($_getTanggal[0]);
		$fromDate = ($fromDate)?$fromDate:date("Y-m-d");

		$toDate = trim($_getTanggal[1]);
		$toDate = ($toDate)?$toDate:date("Y-m-d");
		$searchValue = $_GET['search'];
		
		
		$_DATE = "";
		if ($getTanggal!="")
		{
			$_DATE .= " AND (created_date BETWEEN '".$fromDate." 00:00:00' AND '".$toDate." 23:59:59')";
		}

		$searchValue = $_GET['search'];
		$searchQuery = "";
		if ($searchValue != '')
		{
			$searchQuery = " AND (title like '%".$searchValue."%')";
		}

		//Fetch Records
		$orderBy = " order by created_date desc";
		$sqlQuery = "select * from tickets where id!='0'".$query_author.$searchQuery.$_DATE.$orderBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			
			$petugas = $this->Module->Auth->detailAdmin($row['petugas']);
			$petugasJabatan = $this->Module->Jabatan->detail($petugas['id_jabatan']);
			$start_date = date("d F Y", strtotime($row['start_date']));
			$end_date = date("d F Y", strtotime($row['end_date']));
			if ($row['diteruskan'] != '0') {
				$penerus = $this->Module->Auth->detailAdmin($row['diteruskan']);
				$penerusJabatan = $this->Module->Jabatan->detail($penerus['id_jabatan']);
				$diteruskan = "<br /> |-> Dishare oleh ". $penerus['vName'] . " (".$penerusJabatan['name'].")";
			}
			if ($row['ticket_id'] != '0') {
				$replysurat = $this->Module->Ticket->detail($row['ticket_id']);
				$reply_string = "<br> |-> Reply surat ". $replysurat['title'];
			}
			$author = $this->Module->Auth->detailAdmin($row['author']);
			$jabatan = $this->Module->Jabatan->detail($author['id_jabatan']);
			$authorSurat = $author['vName']." (".$jabatan['name'].")";
			if (($do == 'inbox') AND ($this->DetailAdmin['id'] == $row['petugas'])) {
				$data[] = array(
					"title" => $row['title'].$reply_string,
					'author' => $authorSurat . $diteruskan,
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					'toSent' =>$petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']),
				);
			} else if($do == 'sent' AND $this->DetailAdmin['id'] == $row['author']) {
				$data[] = array(
					"title" =>$row['title'],
					'author' => $author['vName']." (".$jabatan['name'].")",
					'toSent' => $petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date'])
				);
			} else if ($do == 'list_all' AND $this->DetailAdmin['jabatan_slug']=='superadmin') {
				$data[] = array(
					"title" =>$row['title'],
					"author" => $author['vName']." (".$jabatan['name'].")",
					"toSent" => $petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']),
				);
			}
		}

		$path =  '../public/themes/admin/assets/img/logos/logo.png';
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$dataimage = file_get_contents($path);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataimage);
		$this->Template->assign("imageLogo", $base64);

		require_once('../Module/html2pdf.class.php');
		try
		{
			$this->Template->assign("title", "List E-Surat Kabupaten Toba");
			$this->Template->assign("listTicket", $data);
			$showData = $this->Template->ShowAdmin("ticket/ticket_pdf.html");
			//echo $showData;
			$html2pdf = new HTML2PDF('P', 'A4', 'en');
			$html2pdf->pdf->SetDisplayMode('fullpage');
			$html2pdf->writeHTML($showData, isset($_GET['vuehtml']));
			$html2pdf->Output("e_surat-".$fromDate.rand(0,9).rand(0,9).rand(0,9).'.pdf');
		}
		catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
		die();
	}

	function excel()
	{
		$do = $this->Do;
		
		if ($do == 'sent') $query_author = " AND author='".$this->DetailAdmin['id']."'";
		$getTanggal = $_GET['tanggal'];
		$_getTanggal = explode("to", $getTanggal);
		$fromDate = trim($_getTanggal[0]);
		$fromDate = ($fromDate)?$fromDate:date("Y-m-d");

		$toDate = trim($_getTanggal[1]);
		$toDate = ($toDate)?$toDate:date("Y-m-d");
		$searchValue = $_GET['search'];
		
		
		$_DATE = "";
		if ($getTanggal!="")
		{
			$_DATE .= " AND (created_date BETWEEN '".$fromDate." 00:00:00' AND '".$toDate." 23:59:59')";
		}

		$searchValue = $_GET['search'];
		$searchQuery = "";
		if ($searchValue != '')
		{
			$searchQuery = " AND (title like '%".$searchValue."%')";
		}

		//Fetch Records
		$orderBy = " order by created_date desc";
		$sqlQuery = "select * from tickets where id!='0'".$query_author.$searchQuery.$_DATE.$orderBy;
			
		$sqlRecord = $this->Db->sql_query($sqlQuery);
		while ($row = $this->Db->sql_array($sqlRecord))
		{
			$petugas = $this->Module->Auth->detailAdmin($row['petugas']);
			$petugasJabatan = $this->Module->Jabatan->detail($petugas['id_jabatan']);
			$start_date = date("d F Y", strtotime($row['start_date']));
			$end_date = date("d F Y", strtotime($row['end_date']));
			if ($row['diteruskan'] != '0') {
				$penerus = $this->Module->Auth->detailAdmin($row['diteruskan']);
				$penerusJabatan = $this->Module->Jabatan->detail($penerus['id_jabatan']);
				$diteruskan = " |-> Dishare oleh ". $penerus['vName'] . " (".$penerusJabatan['name'].")";
			}
			if ($row['ticket_id'] != '0') {
				$replysurat = $this->Module->Ticket->detail($row['ticket_id']);
				$reply_string = " |-> Reply surat ". $replysurat['title'];
			}
			$author = $this->Module->Auth->detailAdmin($row['author']);
			$jabatan = $this->Module->Jabatan->detail($author['id_jabatan']);
			$authorSurat = $author['vName']." (".$jabatan['name'].")";
			if (($do == 'inbox') AND ($this->DetailAdmin['id'] == $row['petugas'])) {
				$data[] = array(
					"title" => $row['title'].$reply_string,
					'author' => $authorSurat . $diteruskan,
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					'toSent' =>$petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']),
				);
			} else if($do == 'sent' AND $this->DetailAdmin['id'] == $row['author']) {
				$data[] = array(
					"title" =>$row['title'],
					'author' => $author['vName']." (".$jabatan['name'].")",
					'toSent' => $petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date'])
				);
			} else if ($do == 'list_all' AND $this->DetailAdmin['jabatan_slug']=='superadmin') {
				$data[] = array(
					"title" =>$row['title'],
					"author" => $author['vName']." (".$jabatan['name'].")",
					"toSent" => $petugas['vName']." (".$petugasJabatan['name'].")",
					'description' => $row['description'],
					'created_date'=> date('d F Y H:i', strtotime($row['created_date'])),
					"start_date" => $start_date." s/d ".$end_date,
					"lainnya" => $this->Module->Ticket->dateFormat($row['created_date']),
				);
			}
		}

		$Excel = array();
		$Excel[] = array("List E-Surat Kabupaten Toba");

		$Excel[] = array();
		$Excel[] = array('Subject','Dari', 'Kepada', 'Deskripsi','Date');

		for ($i=0;$i<count($data);$i++)
		{
			$Excel[] = array(
				$data[$i]['title'],
				$data[$i]['author'],
				$data[$i]['toSent'],
				$data[$i]['description'],
				$data[$i]['created_date'],
			);
		}
		
		//print_r($Excel);
		$this->LoadModule("Export");
		$this->Module->Export->ExportExcel(array_values($Excel), "e_surat-");
	}

	function detail()
	{
		$Detail = $this->Module->Ticket->detail($this->Id);
		$this->Template->assign("Detail", $Detail);
		$this->Template->assign("action", $_GET['action']);
		if (($Detail['author'] == $this->DetailAdmin['id']) OR ($this->DetailAdmin['id'] == $Detail['petugas']) OR $this->DetailAdmin['jabatan_slug'] == 'superadmin') {
			if ($this->DetailAdmin['id'] == $Detail['petugas']) {
				$this->Module->Ticket->updateUsRead($Detail['id']);
			}
			$this->Module->Notif->updateUsRead($this->DetailAdmin['id'], 'mysurat',$Detail['id']);
			echo $this->Template->ShowAdmin("ticket/detail/detail_index.html");
		} else {
			echo $this->Template->ShowAdmin('404.html');
		}
	}

	function getdetail()
	{
		$idTicket = $this->uri(4);
		$Detail = $this->Module->Ticket->detail($idTicket);
		$this->Template->assign("Detail", $Detail);
		$start_date = date("d F Y", strtotime($Detail['dTanggalSurat']));
		if ($Detail['dDiterimaTanggal']!='0000-00-00')
		$end_date = date("d F Y", strtotime($Detail['dDiterimaTanggal']));
		$created_date = date("d F Y - H:i:s", strtotime($Detail['created_date']));

		$this->Template->assign("dTanggalSurat", $start_date);
		$this->Template->assign("dDiterimaTanggal", $end_date);
		$this->Template->assign("created_date", $created_date);
		$Author = $this->Module->Auth->detailAdmin($Detail['author']);
		$this->Template->assign('Author', $Author);
		$this->Template->assign('jabatan', $this->Module->Jabatan->detail($Author['id_jabatan']));
		$this->Template->assign('listRevisi', $this->Module->Ticket->listRevisi($Detail['id']));
		$this->Template->assign('Penerus', $this->Module->Auth->detailAdmin($Detail['diteruskan']));
		$this->Template->assign('ReplySurat', $this->Module->Ticket->detail($Detail['ticket_id']));
		$this->Template->assign('listFile', json_decode($Detail['attacment'], true));
		$this->Template->assign('listLinkFile', json_decode($Detail['linkFile'], true));
		$this->Template->assign('listFileSignature', json_decode($Detail['signature'], true));
		$getTitle = $this->Template->ShowAdmin("ticket/detail/detail_title.html");
		$getDetail = $this->Template->ShowAdmin("ticket/detail/detail.html");

		$json_data = array(
			'title' => $getTitle, 
			'data' => $getDetail
		);

		echo json_encode($json_data);
	}

	function edit()
	{
		$Detail = $this->Module->Ticket->detail($this->Id);
		$this->Template->assign("Detail", $Detail);
		$this->Template->assign('listFile', json_decode($Detail['attacment'], true));
		$this->Template->assign('linkFile', json_decode($Detail['linkFile'], true));
		echo $this->Template->ShowAdmin("ticket/ticket_edit.html");
	}

	function add()
	{
		$Detail = $this->Module->Ticket->detail($_GET['reply']);
		$this->Template->assign('Reply',$Detail);
		$this->Template->assign('listUser', $this->Module->Auth->listAdmin());
		$this->Template->assign('listJenisSurat', $this->Module->Jenissurat->listAll());
		$this->Template->assign('listFile', json_decode($Detail['attacment'], true));
		$this->Template->assign('linkFile', json_decode($Detail['linkFile'], true));
		$this->Template->assign('listFileSignature', json_decode($Detail['signature'], true));
		$this->Template->assign('action',  $_GET['action']);
		if ($_GET['reply']!= "" AND $_GET['action'] == 'reply')
		echo $this->Template->ShowAdmin("ticket/ticket_reply.html");
		else if ($_GET['reply']!= "" AND $_GET['action'] == 'disposisi') 
		echo $this->Template->ShowAdmin("ticket/ticket_disposisi.html");
		else 
		echo $this->Template->ShowAdmin("ticket/ticket_add.html");
	}

	function revisi() {
		$Detail =$this->Module->Ticket->detail($_GET['surat']);
		$this->Template->assign('Detail', $Detail);
		$array = json_decode($Detail['attacment'],  true);
		$cari = array_search('pdf',array_column($array, 'type'))."";
		//=====html====//
		if ($cari !='') {
			if($_GET['action'] !='edit') {
				$this->Module->Converter->pdftopng($this->dirTicket.$array[$cari]['id'], $Detail['id']);

			}
		}
		$this->Template->assign('File', $this->Module->Converter->readfile($Detail['id']));
		$this->Template->assign('linkFile', json_decode($Detail['linkFile'],  true));
		$this->Template->assign('Surat', $_GET['surat']);
		echo $this->Template->ShowAdmin('ticket/revisi/revisi_add.html');
	}

	function editfilerevisi() {
		$gambar ='../public/upload/temp/revisi_'.$this->Id."/".$_GET['temp'];
		$info = getimagesize($gambar);
		
		$this->Template->assign('Info', $info);
		$this->Template->assign('url_img', $_GET['temp']);
		echo $this->Template->ShowAdmin('ticket/revisi/revisi_file.html');
	}

	function saverevisi() {
		// Mendapatkan data gambar dari AJAX
		$imgData = $_POST['imgData'];
		
		// Menghapus metadata gambar
		$imgData = str_replace('data:image/png;base64,', '', $imgData);
		
		// Mendekode data gambar dari base64 ke binary
		$imgBinaryData = base64_decode($imgData);
		
		// Menyimpan gambar ke dalam file di server
		$file = "../public/upload/temp/revisi_".$this->Id."/".$_GET['temp'];
		file_put_contents($file, $imgBinaryData);
		
		// Membuat respons JSON
		$response = array(
			'status' => 'success',
			'action' => $this->Config['admin']['url'].'mysurat/detail?action=edit&id='.$this->Id // URL untuk download file
		);
		echo json_encode($response);
	}

	function share()
	{
		$this->Template->assign('Detail', $this->Module->Ticket->detail($this->Id));
		$this->Template->assign('listUser', $this->Module->Auth->listAdmin());
		echo $this->Template->ShowAdmin("ticket/ticket_share.html");
	}
	
	function show()
	{
		// echo gettype($_GET['noSurat']); // menampilkan tipe data dari parameter noSurat
		$Detail = $this->Module->Ticket->detail($this->Id);
		$this->Template->assign('noSurat', $_GET['noSurat']);
		$this->Template->assign('Jenissurat', $this->Module->Jenissurat->detail($Detail['jenis_pekerjaan']));
		$this->Template->assign('Disposisi', $this->Module->Ticket->disposisi($_GET['noSurat']));
		$this->Template->assign('listUser', $this->Module->Auth->listAdmin());
		echo $this->Template->ShowAdmin("ticket/ticket_preview.html");
	}

	function submit()
	{
		$author = $this->DetailAdmin['id'];
		$selectfile = $_POST['myfile'];
		$selectfilelink = $_POST['mylink'];
		$title = $_POST['title'];
		$vSifat = $_POST['vSifat'];
		$vNoSurat = $_POST['vNoSurat'];
		$dTanggalSurat = $_POST['dTanggalSurat'];
		$dDiterimaTanggal = $_POST['dDiterimaTanggal'];
		$vFrom = $_POST['vFrom'];
		$vNoAgenda = $_POST['vNoAgenda'];
		$description = $_POST['description'];
		$status = $_POST['status'];
		$diteruskan = $_POST['diteruskan'];
		$petugas = $_POST['petugas'];
        $files = $_FILES['files'];
		$files_signature = $_FILES['files_signature'];
		$gambar =array();
        for ($i = 0; $i < count($files['name']); $i++) {
			$type_array = explode('.', $files['name'][$i]);
            $myfile[$i]  =  array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            );
			$simpanFile = $this->Pile->saveFile($myfile[$i], "file_" . date('YmdHis') . rand(0, 9) . rand(0, 9) . rand(0, 9));
			if ($simpanFile) {
				$gambar[$i] = array(
					'id' => $simpanFile,
					'name' => $myfile[$i]['name'],
					'file_size' => $myfile[$i]['size'],
					'type' => end($type_array)
				);
			}
        }
		$ttd = array();
		for ($i = 0; $i < count($files_signature['name']); $i++) {
			$type_array = explode('.', $files_signature['name'][$i]);
			$myfile[$i] = array(
				'name' => $files_signature['name'][$i],
				'type' => $files_signature['type'][$i],
				'tmp_name' => $files_signature['tmp_name'][$i],
				'error' => $files_signature['error'][$i],
				'size' => $files_signature['size'][$i]
			);
			$simpanFile = $this->Pile->saveFile($myfile[$i], "gambar_" . date('YmdHis') . rand(0, 9) . rand(0, 9) . rand(0, 9));
			if ($simpanFile) {
				$ttd[$i] = array(
					'id' => $simpanFile,
					'name' => $myfile[$i]['name'],
					'file_size' => $myfile[$i]['size'],
					'type' => end($type_array)
				);
			}
		}
		$attachment = json_encode($gambar);  
		$signature = json_encode($ttd);
		$jenis_pekerjaan = $_POST['jenis_pekerjaan'];
		$linkFileList = $_POST['linkFile'];
		for ($i=0; $i < count($linkFileList); $i++) { 
			if($linkFileList[$i] != "") $linkFileFilter[] = $linkFileList[$i];
		}
		$linkFile = json_encode($linkFileFilter);
		$created_date = date("Y-m-d H:i:s");
		$lainnya = $_POST['lainnya'];
		$pakai = array();
		$ticket_id = $_POST['ticket_id'];
 		$Action = $_POST['action'];
		switch ($Action)
		{
			case "add":
				if (($title!="") AND ($description!="") AND ($petugas[0] != ''))
				{
					for ($i=1; $i < count($petugas); $i++) {
						$this->Module->Ticket->add(
							array(
								'author' => $author,
								'title' => $title,
								'description' => $description,
								'status' => $status,
								'petugas' => $petugas[$i],
								'jenis_pekerjaan' => $jenis_pekerjaan,
								'created_date' => $created_date,
								'attacment' => $attachment,
								'signature' => $signature,
								'ticket_id' => $ticket_id,
								'vNoSurat' => $vNoSurat,
								'vNoAgenda' => $vNoAgenda,
								'dDiterimaTanggal' => $dDiterimaTanggal,
								'dTanggalSurat' => $dTanggalSurat,
								'vFrom' => $vFrom,
								'vSifat' => $vSifat,
								'linkFile' => $linkFile
							)
						);
						$ticket[$i] = $this->Module->Ticket->getLastTicket();
					}
						if ($this->Module->Ticket->add(array(
							'author' => $author,
							'title' => $title,
							'description' => $description,
							'status' => $status,
							'petugas' => $petugas[0],
							'jenis_pekerjaan' => $jenis_pekerjaan,
							'created_date' => $created_date,
							'attacment' => $attachment,
							'signature' => $signature,
							'ticket_id' => $ticket_id,
							'vNoSurat' => $vNoSurat,
							'vNoAgenda' => $vNoAgenda,
							'dDiterimaTanggal' => $dDiterimaTanggal,
							'dTanggalSurat' => $dTanggalSurat,
							'vFrom' => $vFrom,
							'vSifat' => $vSifat,
							'linkFile' => $linkFile,
						)))
						{	
							$ticket[0] = $this->Module->Ticket->getLastTicket();
							for($i=0;$i<count($petugas);$i++) {
								$this->Module->Notif->addNotifTicket($petugas[$i], $ticket[$i]['id'], $author);
							}
							$Return = array('status' => 'success',
							'message' => 'Surat telah di tambahkan', 
							'data' => ''
							);
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
							'data' => ''
							);
						}
				}
				else
				{
					$Return = array(
						'status' => 'error',
						'message' => $this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
						'data' => ''
					);
				}
			break;
			case "update":
				if ($gambar[0]['id']!="" || $selectfilelink[0] !="" || $linkFileFilter[0]!='')
				{
					$detailSurat = $this->Module->Ticket->detail($this->Id);
					$array_l = json_decode($detailSurat['linkFile'], true);
					for ($i=0;$i<count($selectfilelink);$i++) {
						$angka = intval($selectfilelink[$i]);
						$pakai_i[$i] = $array_l[$angka];
					}
					$json_l = array_merge($pakai_i, $linkFileFilter);
					$UpdateField = array();
					$old_file = json_decode($detailSurat['attacment'], true);
					$pakai = array();
					for ($i=0;$i<count($selectfile);$i++) {
						$angka = intval($selectfile[$i]);
						$pakai[$i] = $old_file[$angka];
					}
					$merge_atc = array_merge($pakai, $gambar);
					$UpdateField = array_merge($UpdateField,array('attacment' => json_encode($merge_atc)));

					$UpdateField['linkFile'] = json_encode($json_l);
					if ($this->Module->Ticket->update($UpdateField,$this->Id))
					{
						$this->Module->Notif->editNotifTicket($detailSurat['petugas'], $detailSurat['id'], $this->DetailAdmin['id']);
						$Return = array('status' => 'success',
						'message' => 'Surat telah di perbaharui', 
						'data' => ''
						);
					}
					else
					{
						$Return = array('status' => 'error',
						'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
						'data' => ''
						);
					}
				}
				else
				{
					$Return = array('status' => 'error',
					'message' => $this->Template->showMessage('error', 'Ops! Data form isian tidak lengkap'), 
					'data' => ''
					);
				}
			break;
			case "status":
				if (($status!=""))
				{
					$UpdateField = array(
						'status' => $status
					);
					if ($this->Module->Ticket->update($UpdateField,$this->Id))
					{
						$detailTicket = $this->Module->Ticket->detail($this->Id);
						$this->Module->Notif->addTicketStatus($detailTicket['author'], $status, $this->DetailAdmin['id'], $this->Id);
						$Return = array('status' => 'success',
						'message' => 'Status surat telah di perbaharui', 
						'data' => ''
						);
					}
					else
					{
						$Return = array('status' => 'error',
						'message' =>'Ops! Ada error pada database', 
						'data' => ''
						);
					}
				}
				else
				{
					$Return = array('status' => 'error',
					'message' =>'Ops! Data form isian tidak lengkap', 
					'data' => ''
					);
				}
			break;
			case "share":
				if (($diteruskan!=""))
				{
					$UpdateField = $this->Module->Ticket->detail($this->Id);
					for ($i=0; $i < count($UpdateField); $i++) {
						unset($UpdateField[$i]);
					}
					unset($UpdateField['id']);
					$UpdateField['ticket_id'] = $ticket_id;
					$UpdateField['is_read'] = 0;
					$UpdateField['diteruskan'] = $this->DetailAdmin['id'];
					$UpdateField['petugas'] = $petugas[0];
					$UpdateField['created_date'] = date('Y-m-d H:i:s');
					if ($this->Module->Ticket->add($UpdateField))
					{
						$ticket[0] = $this->Module->Ticket->getLastTicket();
						for ($i=1; $i < count($petugas); $i++) {
							$UpdateField['petugas'] = $petugas[$i];
							$this->Module->Ticket->add($UpdateField);
							$ticket[$i] = $this->Module->Ticket->getLastTicket();
						}
						for($i=0;$i<count($petugas);$i++) {
							$this->Module->Notif->addNotifReply($petugas[$i], $ticket[$i]['id'], $UpdateField['author'], $this->DetailAdmin['id']);
						}
						$Return = array('status' => 'success',
						'message' => 'Surat telah di berhasil dishare', 
						'data' => ''
						);
					}
					else
					{
						$Return = array('status' => 'error',
						'message' =>$this->Template->showMessage('error', 'Ops! Ada error pada database'), 
						'data' => ''
						);
					}
				}
				else
				{
					$Return = array('status' => 'error',
					'message' =>$this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
					'data' => ''
					);
				}
			break;
			
			case "disposisi":
				if (($petugas[0]!="" and $description !=""))
				{
					$UpdateField = $this->Module->Ticket->detail($ticket_id);
					for ($i=0; $i < count($UpdateField); $i++) {
						unset($UpdateField[$i]);
					}
					unset($UpdateField['id']);
					$UpdateField['ticket_id'] = $ticket_id;
					$array_r = json_decode($UpdateField['attacment'], true);
					$array_l = json_decode($UpdateField['linkFile'], true);
					for ($i=0;$i<count($selectfile);$i++) {
						$angka = intval($selectfile[$i]);
						$pakai[$i] = $array_r[$angka];
					}
					for ($i=0;$i<count($selectfilelink);$i++) {
						$angka = intval($selectfilelink[$i]);
						$pakai_i[$i] = $array_l[$angka];
					}
					$json_r = array_merge($gambar, $pakai);
					$json_l = array_merge($linkFileFilter, $pakai_i);
					$UpdateField['attacment'] = json_encode($json_r);
					$UpdateField['linkFile'] = json_encode($json_l);
					unset($UpdateField['id']);
					$UpdateField['is_read'] = 0;
					$UpdateField['status'] = 'null';
					$UpdateField['description'] = $description;
					$UpdateField['author'] = $this->DetailAdmin['id'];
					$UpdateField['petugas'] = $petugas[0];
					$UpdateField['created_date'] = date('Y-m-d H:i:s');
					if ($this->Module->Ticket->add($UpdateField))
					{
						$ticket[0] = $this->Module->Ticket->getLastTicket();
						for ($i=1; $i < count($petugas); $i++) {
							$UpdateField['petugas'] = $petugas[$i];
							$this->Module->Ticket->add($UpdateField);
							$ticket[$i] = $this->Module->Ticket->getLastTicket();
						}
						for($i=0;$i<count($petugas);$i++) {
							$this->Module->Notif->addNotifTicket($petugas[$i], $ticket[$i]['id'], $author);
						}
						$Return = array('status' => 'success',
						'message' => 'Surat telah di berhasil dishare', 
						'data' => ''
						);
					}
					else
					{
						$Return = array('status' => 'error',
						'message' =>$this->Template->showMessage('error', 'Ops! Ada error pada database'), 
						'data' => ''
						);
					}
				}
				else
				{
					$Return = array('status' => 'error',
					'message' =>$this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
					'data' => ''
					);
				}
			break;
			
			case "reply":
				if ($description!="")
				{
						$UpdateField = $this->Module->Ticket->detail($ticket_id);
						
						for ($i=0; $i < count($UpdateField); $i++) {
							unset($UpdateField[$i]);
						}
						unset($UpdateField['id']);
						$UpdateField['ticket_id'] = $ticket_id;
						$array_r = json_decode($UpdateField['attacment'], true);
						$array_l = json_decode($UpdateField['linkFile'], true);
						for ($i=0;$i<count($selectfile);$i++) {
							$angka = intval($selectfile[$i]);
							$pakai[$i] = $array_r[$angka];
						}
						for ($i=0;$i<count($selectfilelink);$i++) {
							$angka = intval($selectfilelink[$i]);
							$pakai_i[$i] = $array_l[$angka];
						}
						$json_r = array_merge($gambar, $pakai);
						$json_l = array_merge($linkFileFilter, $pakai_i);
						$UpdateField['attacment'] = json_encode($json_r);
						$UpdateField['created_date'] =$created_date;
						$UpdateField['status'] = 'null';
						$UpdateField['is_read'] = 0;
						$UpdateField['petugas'] = $UpdateField['author'];
						$UpdateField['author'] = $author;
						$UpdateField['description'] = $description;
						if ($this->Module->Ticket->add($UpdateField))
						{	
							$ticket = $this->Module->Ticket->getLastTicket();
							$this->Module->Notif->addNotifReply($UpdateField['petugas'], $ticket['id'], $author);
							$Return = array('status' => 'success',
							'message' => 'Surat telah di tambahkan', 
							'data' => ''
							);
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
							'data' => ''
							);
						}
				}
				else
				{
					$Return = array(
						'status' => 'error',
						'message' => $this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
						'data' => ''
					);
				}
			break;
			case "revisi":
				if ($description!="")
				{
					$Detail = $this->Module->Ticket->detail($ticket_id);
					$array_l = json_decode($Detail['linkFile'], true);
					for ($i=0;$i<count($selectfilelink);$i++) {
						$angka = intval($selectfilelink[$i]);
						$pakai_i[$i] = $array_l[$angka];
					}
					$json_l = $pakai_i;
					if (!empty($json_l)) {
						$link="<br><b>Link File Yang Harus Diperbaiki</b><br><div class=\"list-group mt-0\">";
						for ($i=0; $i < count($json_l); $i++) { 
							$link.="<a class=\"list-group-item list-group-item-action text-primary\" href=\"".$json_l[$i]."\">".$json_l[$i]."</a>";
						}
						$link.="</div>";
					}
					$getfile = $this->Module->Converter->replaceandrename($ticket_id);
					$merge_file = array_merge($getfile, $gambar);
					if ($this->Module->Ticket->addRevisi(array(
						'idTicket' => $ticket_id,
						'tRevisi' => $description.$link,
						'tFile' => json_encode($merge_file),
						'idUser' => $author
						)))
						{	
							$detailTicket = $this->Module->Ticket->detail($ticket_id);
							$this->Module->Ticket->update(array('status' => "rejected"), $ticket_id);
							$this->Module->Notif->addTicketStatus($detailTicket['author'], "rejected", $this->DetailAdmin['id'], $ticket_id);

							$Return = array('status' => 'success',
							'message' => 'Surat telah di tambahkan', 
							'data' => ''
							);
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
							'data' => ''
							);
						}
				}
				else
				{
					$Return = array(
						'status' => 'error',
						'message' => $this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
						'data' => ''
					);
				}
			break;
			case "balasrevisi":
				if ($description!="")
				{
						if ($this->Module->Ticket->addRevisi(array(
							'idRevisi' => $_POST['id'],
							'tRevisi' => $description,
							'idUser' => $author
						)))
						{	
							$detailSurat = $this->Module->Ticket->detail($ticket_id);
							$detailUser = $this->Module->Auth->detailAdmin($detailSurat['author']);
							$jabatan = $this->Module->Jabatan->detail($detailUser['id_jabatan']);
							$this->Module->Notif->addNotif($detailSurat['petugas'],"Pesan balasan dari <b>".$detailUser['vName']."</b> (".$jabatan['name'].") Tentang Revisi Anda", "mysurat", $detailSurat['id']);
							$Return = array('status' => 'success',
							'message' => 'Pesan telah berhasil ditambahkan', 
							'data' => ''
							);
						}
						else
						{
							$Return = array('status' => 'error',
							'message' => $this->Template->showMessage('error', 'Ops! Ada error pada database'), 
							'data' => ''
							);
						}
				}
				else
				{
					$Return = array(
						'status' => 'error',
						'message' => $this->Template->showMessage('error', 'Data form isian tidak lengkap'), 
						'data' => ''
					);
				}
			break;

		}

		echo json_encode($Return);
	}

	function balasrevisi() 
	{
		$this->Template->assign('Detail', $this->Module->Ticket->detailRevisi($this->Id));
		echo $this->Template->ShowAdmin('ticket/revisi/revisi_reply.html');
	}

	function coverter() {
		$type=$_GET['type'];
		$get_= $_GET['file'];
		$format=$_GET['format'];
		$detailSurat = $this->Module->Ticket->detail($this->Id);
		$explode = explode('.',$get_);
		$string = "";
		for($i=0;$i<count($explode);$i++) {if($explode[$i] != $type)$string.=$explode[$i];}
		$file = json_decode($detailSurat['attacment'], true);
		$searc = array_search($string.".".$format, array_column($file, 'id'));
		$cari = $file[$searc]['id'];
		$data = $cari;
		if ($cari == '') {
			if ($format =='pdf') {
				$data = $this->Module->Converter->wordtopdf();
			} else if ($format=='docx') {
				$data = $this->Module->Converter->pdftoword();
			}
		}
		echo "<script>location.href='".$this->Config['base']['url'].$this->dirTicket.$data."'</script>";
	}

	function checkdone() {
		if($this->Module->Ticket->checkdone($_GET['id'])) {
			$Return = array('status' => 'success',
			'message' => 'Surat telah selesai', 
			'data' => '');
		} else {
			$Return = array('status' => 'error',
			'message' => 'Ops! Terjadi Kesalahan', 
			'data' => '');
		}
		echo json_encode($Return);
	}

}

?>