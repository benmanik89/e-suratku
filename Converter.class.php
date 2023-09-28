<?php  if ( ! defined('ONPATH')) exit('No direct script access allowed'); //Mencegah akses langsung ke class
include("vendor/autoload.php");
use \ConvertApi\ConvertApi;
class Converter extends Core
{
	public function __construct()
	{	
		parent::__construct();
	}
	// function wordtohtml($url) {

    //     // Load the Word file
    //     $wordFile = '../public/'.$url;
    //     $phpWord = IOFactory::load($wordFile);

    //     // Save the Word file as HTML
    //     $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
    //     $htmlFile = 'output.html';
    //     $htmlWriter->save($htmlFile);

    //     // Get the HTML content
    //     $htmlContent = file_get_contents($htmlFile);

    //     // Print the HTML content
    //     return $htmlContent;
    // }

    function pdftopng($url, $id) {
        ConvertApi::setApiSecret('s649aJitmyc8lX4L');
        $result = ConvertApi::convert('png', [
                'File' => '../public/'.$url,
                'ImageHeight' => '1200',
                'ImageWidth' => '700',
                'FileName' => 'revisi'
            ], 'pdf'
        );
        mkdir('../public/upload/temp/revisi_'.$id, 0777, true);
        $result->saveFiles('../public/upload/temp/revisi_'.$id.'/');
    }

    function readfile($id){
        // Masukkan path folder yang ingin dihitung file-nya dan diambil daftar file-nya
        $folder_path = "../public/upload/temp/revisi_$id";

        // Menghitung jumlah file dalam folder
        // $num_files = count(glob($folder_path . "/*"));

        // Mengambil daftar file dalam folder
        $list_files = scandir($folder_path);

        // Filter "." dan ".." dari daftar file
        $list_files = array_diff($list_files, array('.', '..'));
        $i=0;
        foreach($list_files as $file){
         $namafile[$i]=$file;
         $i++;
        }
        return $namafile;
    }

    function replaceandrename($id) {
        // Path folder asal
        $source_dir = "../public/upload/temp/revisi_$id";
        
        // Path folder tujuan
        $target_dir = '../public/upload/upload';
        
        // Array of valid extensions
        $extensions = ['png'];
        
        // Memindai direktori sumber dan memilih file-file png
        $files = scandir($source_dir);
        $files = array_diff($files, ['.', '..']);
        $png_files = preg_grep('/\.('.implode('|', $extensions).')$/i', $files);
        
        // Iterasi pada setiap file dan memindahkannya ke direktori target dengan nama baru yang acak
        $i=0;
        foreach($png_files as $file) {
            // Generate random string sebagai nama baru
            $new_name[$i] = array(
                'name' => 'Revisi-'.$i+1,
                'id' => 'revisi_' .date('YmdHis').rand() . '.png',
                'type' => 'png'
            );
            // Memindahkan file dan mengganti namanya
            rename($source_dir . '/' . $file, $target_dir . '/' . $new_name[$i]['id']);
            $i++;
        }

        return $new_name;
        
    }
    // function pdftoword($name_file, $directory) {
    //     $pdf_file = $directory.$name_file;
    //     // Inisialisasi class FPDI untuk membaca file PDF
    //     $pdf = new Fpdi();
    //     $pdf->AddPage();
    //     $pdf->setSourceFile($pdf_file);
    //     $page = $pdf->importPage(1);
    //     $pdf->useTemplate($page);

    //     // Baca konten file PDF
    //     $content = $pdf->Output('', 'S');

    //     // Buat objek PhpWord dan tambahkan konten PDF
    //     $phpWord = new PhpWord();
    //     $section = $phpWord->addSection();
    //     $section->addText(htmlspecialchars_decode($content));

    //     // Simpan file Word
    //     $word_file = $directory.date('YmdHis').rand(0,9).rand(0,9).rand(0,9).'.docx';
    //     $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    //     $objWriter->save($word_file);
    //     return $word_file;
    // }

    // function wordtopdf($name_file, $directory) {
    //     $word_file = $directory.$name_file;
    //     // Buat objek PhpWord dari file Word
    //     $phpWord = IOFactory::load($word_file);

    //     // Simpan konten PhpWord ke HTML
    //     $xmlWriter = IOFactory::createWriter($phpWord , 'HTML');
    //     $content = $xmlWriter->save('php://output');

    //     // Buat objek Dompdf dan tambahkan konten HTML
    //     $dompdf = new Dompdf();
    //     $dompdf->loadHtml($content);

    //     // Proses konversi dan simpan file PDF
    //     $dompdf->render();
    //     $pdf_file = $directory.date('YmdHis').rand(0,9).rand(0,9).rand(0,9).'.docx';
    //     file_put_contents($pdf_file, $dompdf->output());
    //     return $pdf_file;
    // } 

    // function pdftohtml($pdf_path) {
    //     // Convert PDF to plain text
    //     $pdf = new Pdf();
    //     $text = $pdf->getText($pdf_path);
    //     // Clean the text with HTML Purifier
    //     $config = HTMLPurifier_Config::createDefault();
    //     $config->set('Core.Encoding', 'UTF-8');
    //     $purifier = new HTMLPurifier($config);
    //     $clean_text = $purifier->purify($text);

    //     // Create a new HTML document
    //     $dom = new DomDocument('1.0', 'UTF-8');
    //     $dom->loadHTML($clean_text);
    //     return $dom;
    // }

    // function htmltopdf($html) {
    //     $mpdf = new \Mpdf\Mpdf();
    //     $mpdf->WriteHTML($html);
    //     // Output PDF as file
    //     $mpdf->Output('path/to/your/pdf/file', 'F');
    // }
}
?>