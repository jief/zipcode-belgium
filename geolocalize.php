<?php

/**
 * Use Google Maps API to extract lng & lat for each belgian city
 * 
 * @author Jean-Francois Monfort <jf.monfort@gmail.com>
 */

$file = "zipcodes_num_fr.csv";
$new_file = "zipcodes_lonlat.csv";
$new_file_json = "zipcodes_lonlat.json";
$gmaps = "http://maps.googleapis.com/maps/api/geocode/json?";
$export = array();

if (($handle = fopen($file, "r")) !== FALSE) {
  $fp = fopen($new_file, 'w');

  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { 
    $q = $data[0] . ' ' . $data[1]. ' belgium';
    $url = $gmaps . '&address=' . urlencode($q) . '&sensor=false';

    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = json_decode(curl_exec($ch));
    $info = curl_getinfo($ch);
    curl_close($ch);

    if($info['http_code'] == '200') {
      if($output->status == 'OK') {
        $row = array(
          "zip" => $data[0], 
          "city" => mb_convert_case($data[1], MB_CASE_TITLE, "UTF-8"),
          "lng" => $output->results[0]->geometry->location->lng,
          "lat" => $output->results[0]->geometry->location->lat,
        );
        $export[] = $row;
        fputcsv($fp, $row);
      } else {
        print_r($output);
      }
    } else {
      print_r($info);
    }
  }
  fclose($fp);
  fclose($handle);

  $fp = fopen($new_file_json, 'w');
  fwrite($fp, json_encode($export));
  fclose($fp);
}
