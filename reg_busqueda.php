<?PHP
    header("Content-Type:Application/Json");
    include("funciones.php");
    $table = "busquedas";
    $params = $_POST;
    $types = "sss";
    $result = dbInsert($table, $types, $params);
    echo json_encode($result);
?>
