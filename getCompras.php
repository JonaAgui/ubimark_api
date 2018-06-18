<?PHP
    header("Content-type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $Id = $_COOKIE['Id'];
    $sql = "SELECT pe.*,p.nombre_producto, p.tipo_cuenta, p.Id_usuario, p.Id_empresa
            FROM pedido pe 
            JOIN productos p ON p.Id_producto = pe.Id_producto 
            WHERE pe.Id_usuario = ?";
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
        
        switch($row['tipo_cuenta']){
            case "EMPRESA":
                $sql = "SELECT nombre_empresa AS nombre, telefono, calle, numero, cp, colonia, ciudad
                        FROM empresa 
                        WHERE Id_empresa = ?";
                if($query=$enlace->prepare($sql)){
                    $query->bind_param("i",$row['Id_empresa']);
                    $query->execute();
                    $res2 = $query->get_result();
                    $query->close();
                }else{
                    echo json_encode(response(300,sqlError($sql,"i",$Id)));
                    return;
                }
                break;
               
            case "PERSONAL":
                $sql = "SELECT CONCAT(nombre,' ',apellidos) AS nombre, telefono, calle, numero, cp, colonia, delegacion as ciudad
                        FROM usuario 
                        WHERE Id_usuario = ?";
                if($query=$enlace->prepare($sql)){
                    $query->bind_param("i",$row['Id_usuario']);
                    $query->execute();
                    $res2 = $query->get_result();
                    $query->close();
                }else{
                    echo json_encode(response(300,sqlError($sql,"i",$Id)));
                    return;
                }
                break;
        }
        $row['vendedor'] = $res2->fetch_Assoc();
        array_push($result,$row);
        
    }
    echo json_encode(response(200,$result));
?>