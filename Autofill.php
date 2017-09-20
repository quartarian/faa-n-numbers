<?php
require('./vendor/autoload.php');

function fetchAirplane($nNumber) {
    $html = file_get_contents("http://registry.faa.gov/aircraftinquiry/NNum_Results.aspx?nNumberTxt=".$nNumber);
    $dom = pQuery::parseStr($html);

    $make = trim($dom->query('#content_lbMfrName')->html());
    $kitMake = trim($dom->query('#content_Label4')->html());
    if(!empty($kitMake))
        $make = $kitMake;

    return [
        'make'  => $make,
        'model' => trim($dom->query('#content_Label7')->html()),
        'year'  => trim($dom->query('#content_Label17')->html()),
        'owner' => trim($dom->query('#content_lbOwnerName')->html()),
        'city'  => trim($dom->query('#content_lbOwnerCity')->html()),
        'state' => trim($dom->query('#content_lbOwnerState')->html()),
    ];
}

// Read CSV
$csv = array_map('str_getcsv', file('log.csv'));

// Get Airplane Details
for($i=1;$i<count($csv);$i++) {
    $details = fetchAirplane($csv[$i][0]);
    $csv[$i][1] = $details['owner'];
    $csv[$i][2] = $details['city'];
    $csv[$i][3] = $details['state'];
    $csv[$i][4] = $details['year'] ." ". $details['make'] ."/". $details['model'];
}

// Save CSV
$fp = fopen('new.csv','w');
foreach($csv as $row) {
    fputcsv($fp,$row);
}
fclose($fp);

