<?php
if (PHP_SAPI !== 'cli') {
    throw new Exception("This is CLI tool");
}
$inputs = array_slice($argv, 1);
if (empty($inputs)) {
    echo "You need to specify folder";
    exit;
}