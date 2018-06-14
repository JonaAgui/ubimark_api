<?PHP
    header("Content-type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $Id = $_COOKIE['Id'];
    $sql = "SELECT * 
            FROM productos p  
            WHERE Id_usuario = ?";
    if($query = $enlace -> prepare($sql)){
        $query->bind_param("i",$Id);
        $query->execute();
        $res = $query->get_result();
        $query->close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$Id)));
        return;
    }
    $result=array();
    while($row = $res->fetch_Assoc()){
        $sql = "SELECT path, Id_usuario 
                FROM imagen_prod 
                WHERE Id_producto = ? 
                LIMIT 1";
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
        array_push($result,$row);
    }
    echo json_encode(response(200,$result));
?>