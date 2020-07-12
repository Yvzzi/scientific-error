<?php
declare(strict_types = 1);
// require_once __DIR__ . "/../autoload@module.php";
require_once __DIR__ . "/../build/scientific-error.phar";

use error\ScientificError;

ScientificError::init();
ScientificError::hatePath(__DIR__ . "/..");
ScientificError::throwError();

// error 1
$a= 1 / 0;
// echo 5655656;

// error 2
// echo 1 + "";

// error 3
// $b = [];
// $k = $b["zzz"];

// try {
    // error 4
    // $c = null;
    // $d = $c["666"];
// } catch (\Throwable $e) {
//     echo $e->getTraceAsString();
// }

// error 5
// $e = [0, 1];
// $f = $e[5];

// error 6
// function foo(): int {
//     return "sas";
// }
// foo();

// useful
// assert(1 == 5);
// echo 56;

