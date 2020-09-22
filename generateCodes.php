<?php

$options = getopt("", ["count:"]);

if (!$options['count'] || !is_numeric($options['count'])) {
    die("Please run with --count= to specify number of codes to generate!");
}

$total = (int) $options['count'];

$codes = [];
for ($i = 1; $i < $total; $i++) {
    $codes[] = getRandomString(3);
}

$date = new \DateTime();
$fp = fopen(sprintf('voting_codes_%s.csv', time()), 'w');

foreach ($codes as $fields) {
    fputcsv($fp, [$fields]);
}

fclose($fp);

die(sprintf("Generated %s codes" . PHP_EOL, $total));

//
// Utility
// 

/**
 * Generates a cryptographically secure random string
 *
 * @param integer $length
 * @return string
 */
function getRandomString($length)
{
    $out = "";
    for ($i = 0; $i <= $length; $i++) {
        $out .= crypt(random_int(0, 9999999999999), random_int(0, 9999999999999));
    }
    return $out;
}
