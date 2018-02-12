<?php

ini_set('memory_limit', '1G');

$zip = __DIR__.'/inventory_feeds/dealerspecialties/'.date('ymd').'.zip';
$csv = __DIR__.'/inventory_feeds/dealerspecialties/dealerspecialties.csv';

$zip = zip_open($zip);
if (!is_resource($zip)) die('Zip file is not a legit zip file!');
$vehicles_handle = fopen('php://temp', 'r+');
$links_handle = fopen('php://temp', 'r+');

$i = 0;
while ($zip_read = zip_read($zip)) {
    $zip_entry_name = zip_entry_name($zip_read);
    if ($zip_entry_name == 'VEHICLES.TXT') {
        fwrite($vehicles_handle, zip_entry_read($zip_read,999999999));
        rewind($vehicles_handle);
    } else if ($zip_entry_name == 'LINKS.TXT') {
        fwrite($links_handle, zip_entry_read($zip_read,999999999));
        rewind($links_handle);
    }
    if ($i++ > 10) break;
}

$vehicles = [];
if ($vehicles_handle AND $links_handle) {

    // fields
    $links_fields = fgetcsv($links_handle, 0, ",");
    unset($links_fields[0], $links_fields[1]);
    $vehicles_fields = fgetcsv($vehicles_handle, 0, ",");
    $fields = array_merge($vehicles_fields, $links_fields);


    // links data
    $links_data = [];
    while(($data = fgetcsv($links_handle, 0, ",")) !== FALSE) {
        if (empty($data[0]) OR empty($data[1])) continue;
        $vin = $data[1];
        unset($data[0], $data[1]);
        $links_data[$vin] = $data;
    }
    $links_data_blank = array_fill(0, count(current($links_data)), '');


    // vehicles data
    while (($data = fgetcsv($vehicles_handle, 0, ",")) !== FALSE) {
        if (empty($data[0]) OR empty($data[1])) continue;
        $dealer_id = $data[0];
        $vin = $data[1];
        $link_data = empty($links_data[$vin]) ? $links_data_blank : $links_data[$vin];
        $vehicles[] = array_merge($data,$link_data);
    }

    $csv_handle = fopen($csv, 'w');
    fputcsv($csv_handle,$fields);
    foreach($vehicles AS $vehicle) {
        fputcsv($csv_handle,$vehicle);
    }
    echo 'Made '.$csv.PHP_EOL;


    fclose($csv_handle);
    fclose($vehicles_handle);
    fclose($links_handle);
}
