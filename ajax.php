<?php

// SELECT UNIX_TIMESTAMP(timestamp - SECOND(timestamp)) as timestamp, device, sensor, humidity, temperature FROM hcr.records ORDER BY device, timestamp, sensor;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $mysqli = new mysqli('localhost', 'hcr', NULL, 'hcr');
    if ($mysqli->connect_error) {
        die('{"error":"server","msg":"Datenbankfehler: #' . $mysqli->connect_errno . ' ' . $mysqli->connect_error . '"}');
    }
    $mysqli->set_charset('utf8');

    $statement = $mysqli->prepare("SELECT UNIX_TIMESTAMP(timestamp - SECOND(timestamp)) as timestamp, device, sensor, humidity, temperature FROM hcr.records ORDER BY device, timestamp, sensor");
    $statement->execute();
    $statement->bind_result($timestamp, $device, $sensor, $humidity, $temperature);

    $json = array();
    $temp_timestamp = 0;
    $temp_data = array();
    $temp_device = NULL;
    $temp_device_data = array();

    while ($statement->fetch()) {
        if ($temp_device == NULL) {
            $temp_device = $device;
        } else if ($temp_device != $device) {
            $json_device = addDeviceData($temp_device, $temp_device_data, $temp_timestamp, $temp_data);
            array_push($json, $json_device);
            //RESET
            $temp_device_data = array();
            $temp_device = $device;
            $temp_timestamp = 0;
            $temp_data = array();
        }
        if ($temp_timestamp == 0) {
            $temp_timestamp = $timestamp;
        } else if (!(($temp_timestamp - 60) <= $timestamp && $timestamp <= ($temp_timestamp + 60))) {
            // bisher angesammelte Daten verarbeiten
            $data = calcAverageRange($temp_timestamp, $temp_data);
            array_push($temp_device_data, $data);

            // RESET
            $temp_timestamp = $timestamp;
            $temp_data = array();
        }
        $element = array();
        $element['timestamp'] = $timestamp;
        $element['device'] = $device;
        $element['sensor'] = $sensor;
        $element['humidity'] = floatval($humidity);
        $element['temperature'] = floatval($temperature);
        array_push($temp_data, $element);

    }
    $json_device = addDeviceData($temp_device, $temp_device_data, $temp_timestamp, $temp_data);
    array_push($json, $json_device);
    $statement->close();

    echo json_encode($json);
}

function calcAverageRange($temp_timestamp, $temp_data) {
    $data = array();
    $temp_humidity = 0;
    $temp_humidity_min = INF;
    $temp_humidity_max = 0;
    $temp_temperature = 0;
    $temp_temperature_min = INF;
    $temp_temperature_max = 0;
    foreach ($temp_data as $value) {
        $temp_humidity += $value['humidity'];
        if (($value['humidity']) < $temp_humidity_min) {
            $temp_humidity_min = $value['humidity'];
        }
        if (($value['humidity']) > $temp_humidity_max) {
            $temp_humidity_max = $value['humidity'];
        }
        $temp_temperature += $value['temperature'];
        if (($value['temperature']) < $temp_temperature_min) {
            $temp_temperature_min = $value['temperature'];
        }
        if (($value['temperature']) > $temp_temperature_max) {
            $temp_temperature_max = $value['temperature'];
        }
    }
    $data['timestamp'] = $temp_timestamp;
    $data['humidity'] = $temp_humidity / sizeof($temp_data);
    $data['humidity_min'] = (($temp_humidity_max - 5) < 0) ? 0 : $temp_humidity_max - 5;
    $data['humidity_max'] = (($temp_humidity_min + 5) > 100) ? 100 : $temp_humidity_min + 5;
    $data['temperature'] = $temp_temperature / sizeof($temp_data);
    $data['temperature_min'] = $temp_temperature_max - 2;
    $data['temperature_max'] = $temp_temperature_min + 2;
            
    return $data;
}

function addDeviceData($temp_device, $temp_device_data, $temp_timestamp, $temp_data) {
    $data = calcAverageRange($temp_timestamp, $temp_data);
    array_push($temp_device_data, $data);
    $json_device = array();
    $json_device['device'] = $temp_device;
    $json_device['data'] = $temp_device_data;
    return $json_device;
}
