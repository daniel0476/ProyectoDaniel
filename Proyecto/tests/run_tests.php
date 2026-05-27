<?php
/**
 * run_tests.php
 * Ejecuta todas las pruebas del proyecto.
 */

// Activar aserciones con lanzamiento de excepción
ini_set('assert.exception', 1);
assert_options(ASSERT_ACTIVE, 1);
// Detener al primer fallo
assert_options(ASSERT_BAIL, 1);

require_once __DIR__ . '/../funciones.php';

// Inicializar sesión si aún no se ha hecho (necesaria para algunas funciones)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Buscar todos los archivos de prueba que empiecen por test_
$tests = glob(__DIR__ . '/test_*.php');

if (empty($tests)) {
    echo "No se encontraron pruebas.\n";
    exit(1);
}

// Ejecutar cada archivo de prueba secuencialmente
foreach ($tests as $test) {
    echo "Ejecutando " . basename($test) . "... ";
    require $test;
    echo "OK\n";
}

echo "\nTodas las pruebas pasaron.\n";
