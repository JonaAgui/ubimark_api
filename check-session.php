<?PHP

    header("Content-type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    echo json_encode(check_session());

?>