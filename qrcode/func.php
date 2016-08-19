<?php    




function create_qrcode($m_id,$data)
{
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    $PNG_WEB_DIR = 'qrcode/temp/';
    
    require_once dirname(__FILE__).DIRECTORY_SEPARATOR."qrlib.php";    

    if (!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR);    
    $filename = $m_id;
    
    if(!$data) $data='no data';
    $errorCorrectionLevel = 'M';    
    $matrixPointSize = 4;

    $filename = $PNG_TEMP_DIR.$filename.'.png';
    QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    
    return $PNG_WEB_DIR.basename($filename);
}
    