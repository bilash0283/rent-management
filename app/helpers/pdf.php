<?php 
use Dompdf\Dompdf;

function generateInvoicePDF($html){
 $dompdf = new Dompdf();
 $dompdf->loadHtml($html);
 $dompdf->render();
 $dompdf->stream("invoice.pdf");
}


?>