<?php
/**
 * run_tests.php
 * Ejecuta todas las pruebas del proyecto.
 */

ini_set('assert.exception', 1);
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_BAIL, 1);

require_once __DIR__ . '/../funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tests = glob(__DIR__ . '/test_*.php');

if (empty($tests)) {
    echo "No se encontraron pruebas.\n";
    exit(1);
}

foreach ($tests as $test) {
    echo "Ejecutando " . basename($test) . "... ";
    require $test;
    echo "OK\n";
}

echo "\nTodas las pruebas pasaron.\n";
