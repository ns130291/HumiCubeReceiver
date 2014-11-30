<?php

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
    // Token check
    $mysqli = new mysqli('localhost', 'hcr', NULL, 'hcr');
    if ($mysqli->connect_error) {
        die('{"error":"server","msg":"Datenbankfehler: #' . $mysqli->connect_errno . ' ' . $mysqli->connect_error . '"}');
    }
    $mysqli->set_charset('utf8');

    $token = get('token');
    $statement = $mysqli->prepare("SELECT token FROM tokens WHERE token = ?");
    $statement->bind_param("s", $token);
    $statement->execute();
    $statement->bind_result($result);

    if (!$statement->fetch()) {
        $json = array();
        $json['error'] = 'wrong_token';
        $json['msg'] = 'API error: wrong \'token\'';
        die(json_encode($json));
    }
    $statement->close();
    
    $device = strip_tags(get('device'));
    $humidity = get('humidity');
    $temperature = get('temperature');
    $sensor = strip_tags(get('sensor', TRUE));
    $statement = $mysqli->prepare("INSERT INTO records (device, timestamp, humidity, temperature, sensor) VALUES (?, NOW(), ?, ?, ?)");
    $statement->bind_param("sdds", $device, $humidity, $temperature, $sensor);
    if ($statement->execute()) {
        $json = array();
        $json['inserted'] = 'true';
        echo json_encode($json);
    } else {
        $json = array();
        $json['inserted'] = 'false';
        $json['error'] = 'insert_error';
        $json['msg'] = 'API error: ' . $statement->error;
        die(json_encode($json));
    }
} else {
    $json = array();

    $json['error'] = 'wrong_method';
    $json['msg'] = 'Only GET and POST requests are accepted';

    echo json_encode($json);
}

function get($name, $empty = FALSE) {
    if ($empty) {
        if (!isset($_REQUEST[$name])) {
            return NULL;
        } else {
            return $_REQUEST[$name];
        }
    }
    if (!isset($_REQUEST[$name])) {
        $json = array();
        $json['error'] = $name . '_param_missing';
        $json['msg'] = 'API error: \'' . $name . '\' parameter missing';
        die(json_encode($json));
    }
    if ($_REQUEST[$name] === '' || $_REQUEST[$name] === NULL) {
        $json = array();
        $json['error'] = $name . '_param_empty';
        $json['msg'] = 'API error: \'' . $name . '\' parameter empty';
        die(json_encode($json));
    }
    return $_REQUEST[$name];
}
