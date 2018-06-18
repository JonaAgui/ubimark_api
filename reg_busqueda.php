<?PHP
    header("Content-Type:Application/Json");
    include("funciones.php");
    $log = getLogger("Reg_busqueda");
    $table = "busquedas";
    $params = $_POST;
    $types_ref = array("busqueda"=>"s","coords"=>"s","estado"=>"s","Id_localizacion"=>"i");
    $types = getTypes($params,$types_ref);
    $result = dbInsert($table, $types, $params);
    echo json_encode($result);
?>
