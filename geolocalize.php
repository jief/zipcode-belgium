<?php
require 'vendor/autoload.php';


/**
 * Use Openstreetmap API to extract lng & lat for each belgian city
 *
 * @author Jean-Francois Monfort <jf.monfort@gmail.com>
 */

$file = "zipcodes_num_fr.csv";
$new_file = "zipcode-belgium.csv";
$new_file_json = "zipcode-belgium.json";
$export = array();

if (($handle = fopen($file, "r")) !== FALSE) {
  $fp = fopen($new_file, 'w');

  $geocoder = new \Geocoder\Geocoder();
  $adapter = new \Geocoder\HttpAdapter\CurlHttpAdapter();
  $chain = new \Geocoder\Provider\ChainProvider(array(
    new \Geocoder\Provider\OpenStreetMapProvider($adapter, 'be'),
  ));
  $geocoder->registerProvider($chain);

  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if($data[2] == "") {
      continue;
    }

    try {
      $addr = $data[0] . ' ' . $data[1]. ' Belgium';
      $geocode = $geocoder->geocode($addr);

      $row = array(
        "zip" => $data[0],
        "city" => mb_convert_case($data[1], MB_CASE_TITLE, "UTF-8"),
        "lng" => $geocode->getLongitude(),
        "lat" => $geocode->getLatitude(),
      );
      $export[] = $row;
      fputcsv($fp, $row);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    sleep(1);
  }
  fclose($fp);
  fclose($handle);

  $fp = fopen($new_file_json, 'w');
  fwrite($fp, json_encode($export));
  fclose($fp);
}
