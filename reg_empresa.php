<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $table = "empresa";
    $params = $_POST;
    $params['Id_usuario'] = $_COOKIE['Id'];
    $params['pais'] = "Mexico";
    $params['calificacion'] = 3;
    $types = "";
    foreach($params as $key => $val){
        if($key != "numinterior"){
            if(!notNull($val)){
                echo json_encode(response(302,$key));
            }
        }
        if($key == "numero" || $key == "cp" || $key == "Id_usuario"){
            $types .= "i";
        }else{
            $types .= "s";
        }
    }
    
    $filename = "intranet/empresas/" . $params['nombre_empresa'] . "/uploads/";
    if (!file_exists($filename)) {
        mkdir($filename, 0777, true);
    }
    foreach($_FILES as $key => $val){
        if($val['error'] == 0){
            $extension = substr($_FILES[$key]['name'], strrpos($_FILES[$key]['name'], '.'), strlen($_FILES[$key]['name']));
            $nombre = $key.$params['nombre_empresa'].date('zwjtnyGis').$params['Id_usuario'].$extension;
            $tipo = $_FILES[$key]['type'];
            $tam = $_FILES[$key]['size'];
            $file = "intranet/empresas/".$params['nombre_empresa'].'/uploads/'.$nombre;
            $tmp_img = $_FILES[$key]['tmp_name'];
            move_uploaded_file($tmp_img, $file);
            $params[$key] = $nombre;
            $types .= "s";
        }
    }
    $result = dbInsert($table,$types,$params);
    if($result['status_code'] != 200){
        echo json_encode($result);
        return;
    }
    $Id_empresa = $result['data']['ID'];

    $params2 = array("trabaja_en" => $Id_empresa);
    $result = dbUpdate("usuario","i",$params2,"i",array("Id_usuario" => $params['Id_usuario']));
    if($result['status_code'] != 200){
        echo json_encode($result);
        return;
    }
    echo json_encode(response(200,array("path" => $filename)));
?>