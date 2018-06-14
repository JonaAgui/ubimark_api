<?PHP
    header("Content-type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $nombre_empresa = $_POST['nombre_empresa'];
    $sql =  "SELECT * FROM empresa WHERE nombre_empresa = ?";
    if($query = $enlace -> prepare($sql)){
        $query -> bind_param("s", $nombre_empresa);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"s",$nombre_empresa)));
        return;
    }
    
    $result = $res -> fetch_Assoc();

    $sql = "SELECT * FROM productos WHERE Id_empresa = ?";
    if($query = $enlace -> prepare($sql)){
        $query->bind_param("i",$result['Id_empresa']);
        $query->execute();
        $res = $query->get_result();
        $query->close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$result['Id_empresa'])));
        return;
    }
    $productos=array();
    while($row = $res->fetch_Assoc()){
        $sql = "SELECT path, Id_usuario FROM imagen_prod WHERE Id_producto = ? LIMIT 1";
        if($query=$enlace->prepare($sql)){
            $query->bind_param("i",$row['Id_producto']);
            $query->execute();
            $query->bind_result($path,$id_usuario);
            $query->fetch();
            $query->close();
        }else{
            echo json_encode(response(300,sqlError($sql,"i",$Id)));
            return;
        }
        $row['imagen']=array("path" => $path,"Id_usuario" => $id_usuario);
        array_push($productos,$row);
    }
    $result['productos'] = $productos;
    echo json_encode(response(200,$result));
?>