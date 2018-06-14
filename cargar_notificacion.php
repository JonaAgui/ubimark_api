<?PHP
    header("Content-type:application/json");
    include_once("conectar.php");
    include("funciones.php");

    $id = $_COOKIE['Id'];
    $notificacion = $_POST['notificacion'];
    $sql = "SELECT * FROM notificaciones WHERE destino = ? AND Id_notificacion = ?";
    if($query = $enlace -> prepare($sql)){
        $query -> bind_param("ii",$id,$notificacion);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",array("Destino"=>$id))));
        return;
    }

    $row = $res-> fetch_assoc();
    if(strcmp($row['tipo'],"PREGUNTA")==0){
        $sql2 = "SELECT p.pregunta,i.path,i.Id_usuario ,if(count(pe.Id_empresa)> 0, 'EMPRESA' , 'PERSONAL')as tipo_vendedor
                FROM preguntas p 
                JOIN imagen_prod i ON i.Id_producto = p.Id_producto
                LEFT JOIN preguntas_empresa pe ON pe.Id_pregunta = p.Id_pregunta
                LEFT JOIN preguntas_personal pp ON pp.Id_pregunta = p.Id_pregunta
                WHERE p.Id_pregunta = ?";
        if($query = $enlace -> prepare($sql2)){
            $query -> bind_param("i",$row['origen']);
            $query -> execute();
            $query -> bind_result($mensaje,$ruta,$autor_img,$vendedor);
            $query -> fetch();
            $query -> close();
        }else{
            echo json_encode(response(300,sqlError($sql2,"i",array("Id_pregunta"=>$row['origen']))));
            return;
        }
        $row['mensaje'] = $mensaje;
        $row['ruta_img'] = $ruta;
        $row['autor_img'] = $autor_img;
        $notificacion = $row;
    }
    echo json_encode(response(200,$notificacion));

?>