<?PHP

    header("Content-type:application/json");
    include_once("conectar.php");
    include("funciones.php");
    echo json_encode(check_session());

?>