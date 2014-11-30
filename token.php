<?php

$token = generate($_GET['len']);
echo $token;

// SOURCE: http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
function generate($length) {
    $token = '';
    
    $alphabet = implode(range('a', 'z')) . implode(range('A', 'Z')) . implode(range(0, 9));

    $alphabetLength = strlen($alphabet);
    
    for ($i = 0; $i < $length; $i++) {
        $randomKey = getRandomInteger(0, $alphabetLength);
        $token .= $alphabet[$randomKey];
        //$token .= alphabet[1];
    }

    return $token;
}

function getRandomInteger($min, $max) {
    $range = ($max - $min);

    if ($range < 0) {
// Not so random...
        return $min;
    }

    $log = log($range, 2);

// Length in bytes.
    $bytes = (int) ($log / 8) + 1;

// Length in bits.
    $bits = (int) $log + 1;

// Set all lower bits to 1.
    $filter = (int) (1 << $bits) - 1;

    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));

// Discard irrelevant bits.
        $rnd = $rnd & $filter;
    } while ($rnd >= $range);

    return ($min + $rnd);
}

