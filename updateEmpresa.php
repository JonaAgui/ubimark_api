<?PHP 
    header("Content-Type:application/json");
    include_once("conectar.php");
    include_once("funciones.php");
    $paramsRequired = array("NotNull"=>array("coordenadas","nombre_empresa","email","telefono","RFC","cp","calle","numero","ciudad","delegacion","estado"),"Null"=>array("numinterior")); 
    $params = $_POST;
    if(($result = paramsRequired($params,$paramsRequired))['status_code'] != 200){
        echo json_encode($result);
        return;
    }
    $Id = $_COOKIE['Id'];
    $sql = "SELECT Id_empresa FROM empresa WHERE Id_usuario = ?";
    if($query = $enlace -> prepare($sql)){
        $query -> bind_param("i",$Id);
        $query -> execute();
        $query -> bind_result($Id_empresa);
        $query -> fetch();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$Id)));
        return;
    }
    $types="sssssisisssss";
    $result = dbUpdate("empresa",$types,$params,"i",array("Id_empresa"=>$Id_empresa));

    echo json_encode($result);
?>