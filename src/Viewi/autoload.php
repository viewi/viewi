<?php

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Viewi\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    // print_r($class);
    // echo '<br>';
    // print_r($prefix);
    // echo '<br>';
    // get the relative class name
    $relative_class = substr($class, $len);
    // print_r($relative_class);
    // echo '<br>';
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // print_r($file);
    // echo '<br>';
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
