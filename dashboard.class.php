<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class

class dashboard extends Core
{
	var $Submit, $Action, $Id, $adminURL, $DetailAdmin, $getTahun;
	public function __construct()
	{
		parent::__construct();
		
		$this->LoadModule("Date");
		$this->LoadModule("Paging");
		$this->LoadModule("Jenis");
		$this->LoadModule("Opd");
		$this->LoadModule("Paket");

		//Load General Process
		include '../inc/general_admin.php';
		$this->Module->Paging->setPaging(21,5);

		$this->adminURL = $this->Config['base']['url'].$this->Config['index']['page'].$this->Config['base']['admin']."/";
		$this->Template->assign("Signature", "dashboard");
		ob_clean();
	}
	
	function main()
	{
			echo $this->Template->ShowAdmin("dashboard/dashboard.html");
	}

	function graph_bulanan()
	{
		$Jan = $this->Module->Paket->month_pagu("01");
		$Feb = $this->Module->Paket->month_pagu("02");
		$Mar = $this->Module->Paket->month_pagu("03");
		$Apr = $this->Module->Paket->month_pagu("04");
		$May = $this->Module->Paket->month_pagu("05");
		$Jun = $this->Module->Paket->month_pagu("06");
		$Jul = $this->Module->Paket->month_pagu("07");
		$Agt = $this->Module->Paket->month_pagu("08");
		$Sep = $this->Module->Paket->month_pagu("09");
		$Okt = $this->Module->Paket->month_pagu("10");
		$Nov = $this->Module->Paket->month_pagu("11");
		$Dec = $this->Module->Paket->month_pagu("12");

		$Year = $this->Module->Paket->year_pagu(date("Y"));

		$percent[0] = round(($Jan['total']/$Year['total'])*100);
		$percent[1] = round(($Feb['total']/$Year['total'])*100);
		$percent[2] = round(($Mar['total']/$Year['total'])*100);
		$percent[3] = round(($Apr['total']/$Year['total'])*100);
		$percent[4] = round(($May['total']/$Year['total'])*100);
		$percent[5] = round(($Jun['total']/$Year['total'])*100);
		$percent[6] = round(($Jul['total']/$Year['total'])*100);
		$percent[7] = round(($Agt['total']/$Year['total'])*100);
		$percent[8] = round(($Sep['total']/$Year['total'])*100);
		$percent[9] = round(($Okt['total']/$Year['total'])*100);
		$percent[10] = round(($Nov['total']/$Year['total'])*100);
		$percent[11] = round(($Dec['total']/$Year['total'])*100);

		$detail = array(
			'jan' => $percent[0],
			'feb' => $percent[1],
			'mar' => $percent[2],
			'apr' => $percent[3],
			'may' => $percent[4],
			'jun' => $percent[5],
			'jul' => $percent[6],
			'agt' => $percent[7],
			'sep' => $percent[8],
			'okt' => $percent[9],
			'nov' => $percent[10],
			'des' => $percent[11],
		);
		$json_data = array('detail' => $detail);
		echo json_encode($json_data);	
	}

	function graph_multiyears()
	{
		$getTahun = ($_GET['tahun'])?$_GET['tahun']:date("Y");

		$totalAll = 0;
		for ($i=0;$i<=3;$i++)
		{
			$yearNow[$i] = ($getTahun-$i);
			$Year[$i] = $this->Module->Paket->year_pagu($yearNow[$i]);
			$totalAll = $totalAll + $Year[$i]['total'];
		}

		for ($i=0;$i<=3;$i++)
		{
			$percent = round(($Year[$i]['total']/$totalAll)*100);
			$detail[$i] = array(
				'tahun' => $yearNow[$i], 
				'percent' => $percent,
			);
		}

		$json_data = array('detail' => $detail);
		echo json_encode($json_data);	
	}

	function paket_pengadaan()
	{
		$getTahun = ($_GET['tahun'])?$_GET['tahun']:date("Y");

		$Tender = $this->Module->Paket->count_paket("tender", $getTahun);
		$Seleksi = $this->Module->Paket->count_paket("seleksi", $getTahun);
		$E_Purchasing = $this->Module->Paket->count_paket("e-purchasing", $getTahun);
		$Penunjukan_Langsung = $this->Module->Paket->count_paket("pl", $getTahun);

		$totalAll = $Tender['total']+$Seleksi['total']+$E_Purchasing['total']+$Penunjukan_Langsung['total'];

		$percent[0] = round(($Tender['total']/$totalAll)*100);
		$percent[1] = round(($Seleksi['total']/$totalAll)*100);
		$percent[2] = round(($E_Purchasing['total']/$totalAll)*100);
		$percent[3] = round(($Penunjukan_Langsung['total']/$totalAll)*100);

		$detail = array(
			'tender' => $percent[0],
			'seleksi' => $percent[1],
			'purchasing' => $percent[2],
			'pl' => $percent[3],
		);

		$json_data = array('detail' => $detail);
		echo json_encode($json_data);	
	}

	private function isJSON($string){
	   return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
	
}

?>