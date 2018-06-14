<?php

$enlace = mysqli_connect("localhost", "id3900810_ubimark_admin", "Pr0yM0dular;", "id3900810_ubimark");
$acentos = $enlace->query("SET NAMES 'utf8'");

if (!$enlace) {
    echo "Error: No se pudo conectar a MySQL.";
    echo "errno de depuración: " . mysqli_connect_errno();
    exit;
}

/**
 * Función para obtener la conección a la bd
 *
 * @return object Conección a la bd
 */
function getDBConnection(){
    return $GLOBALS['enlace'];
}

?>
