<?PHP
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $Id = $_COOKIE['Id'];
    $sql = "SELECT c.*,p.precio,p.existencias,p.nombre_producto FROM carrito c JOIN productos p ON p.Id_producto = c.Id_producto WHERE c.Id_usuario = ?";
    if($query = $enlace -> prepare($sql)){
        $query->bind_param("i",$Id);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$Id)));
        return;
    }
    $table = "pedido";
    $types = "iiidsss";
    
    while($row = $res -> fetch_Assoc()){
        if($row['existencias']<$row['cantidad']){
            echo json_encode(response(304,array("nombre_producto"=>$row['nombre_producto'])));
            return;
        }
        $params = array(); 
        $params['Id_usuario'] = $Id;
        $params['Id_producto'] = $row['Id_producto'];
        $params['cantidad'] = $row['cantidad'];
        $params['total'] = $row['cantidad'] * $row['precio'];
        $params['fecha'] = date("y-m-d");
        $params['estado'] = "NO ENVIADO";
        $params['fecha_entrega'] = "00-00-00";
        $result = dbInsert($table,$types,$params);
        if($result['status_code']==200){
            $result = dbDelete("carrito","i",array("folio_carrito"=>$row['folio_carrito']));
            if($result['status_code'] != 200){
                echo json_encode($result);
                return;  
            }
            $result = dbUpdate("productos","i",array("existencias" => $row['existencias']-$row['cantidad']),"i",array("Id_producto"=>$row['Id_producto']));
            if($result['status_code'] != 200){
                echo json_encode($result);
                return;  
            }
        } else{
            echo json_encode($result);
            return;
        }
    }
    echo json_encode(response(200));
?>