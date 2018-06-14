<?PHP
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php"); 
    
    //Datos del producto a subir
    $table = "productos";
    $params = $_POST;
    $params['Id_usuario'] = $_COOKIE['Id'];
    $types = "";
    $values=array();
    if($params['tipo_cuenta'] == "EMPRESA"){
        $sql = "SELECT trabaja_en FROM usuario WHERE Id_usuario = ?";
        if($query = $enlace -> prepare($sql)){
            $query -> bind_param("i",$params['Id_usuario']);
            $query -> execute();
            $query -> bind_result($Id_empresa);
            $query -> fetch();
            $query -> close();
        }else{
            echo json_encode(sqlError($sql,"i",$params['Id_usuario']));
        }
        $params['Id_empresa'] = $Id_empresa;
    }

    foreach($params as $key => $val){
        if($key != "etiquetas"){
            if($key == 'precio'){
                $types.="d";
            }else if($key == "Id_usuario"){
                $types.="i";
            }else{
                $types.="s";
            }
            $values[$key]=$val;
        }
    }
    //Se crea el registro en la tabla producto
    $result = dbInsert($table,$types,$values);
    if($result['status_code'] != 200){
        echo json_encode($result);
        return;
    }

    $sql = "SELECT Id_producto FROM productos WHERE nombre_producto = ? AND Id_usuario = ? AND precio = ? and existencias = ? and descripcion = ?";
    if($query=$enlace->prepare($sql)){
        $query->bind_param("siiis",$params['nombre_producto'],$params['Id_usuario'],$params['precio'],$params['existencias'],$params['descripcion']);
        $query->execute();
        $query->bind_result($id_prod);
        $query->fetch();
        $query->close();
    }else{
        echo json_encode(response(300,sqlError($sql,"siiis",array($params['nombre_producto'],$params['Id_usuario'],$params['precio'],$params['existencias'],$params['descripcion'])) ));
        return;
    }
    $i=0;
    foreach($_FILES as $key => $val){
        
        if($val['error']==0){
            $extension = substr($_FILES[$key]['name'],strrpos($_FILES[$key]['name'],'.'),strlen($_FILES[$key]['name']));
            $nombre = date('zwjtnyGis').$params['Id_usuario'].$id_prod.$i.$extension ;
            $tipo = $_FILES[$key]['type'];
            $tam = $_FILES[$key]['size'];
            $file="../intranet/usuarios/".$params['Id_usuario'].'/uploads/'.$nombre;
            $tmp_img = $_FILES[$key]['tmp_name'];
            move_uploaded_file($tmp_img, $file);

            $values4 = array("path"=>$nombre,"Id_producto"=>$id_prod,"Id_usuario"=>$params['Id_usuario']);
            $type4 = "sii";
            $table4 = "imagen_prod";
            $result = dbInsert($table4,$type4,$values4);
            if($result['status_code'] != 200){
                echo json_encode($result);
                return;
            }
            $i++;
        }
    }
    

    //Se busca las etiquetas del producto
    $etiquetas = $_POST["etiquetas"];
    
    $tags = preg_split('/[,]+/',$etiquetas);
    $tags_id = array();
    foreach($tags as &$tag){
        $tag = trim($tag);
        $tag = strtoupper($tag);
        $sql = "SELECT Id_tag FROM tags WHERE tag LIKE ?";
        if($query=$enlace->prepare($sql)){
            $query->bind_param("s",$tag);
            $query->execute();
            $query->bind_result($id_tag);
            $query->fetch();
            $query->close();
        }else{
            echo json_encode(response(300,array("sql" => $sql, "params" => array("s",$tag))));
            return;
        }
        //Si la etiqueta existe la guarda si no crea una nueva etiqueta en la tabla tags
        if(notNull($id_tag)){
            array_push($tags_id,$id_tag);
        }else{
            $table2 = "tags";
            $params2=array();
            $params2['tag'] = $tag;
            $types2 = "s";
            $result2 = dbInsert($table2,$types2,$params2);
            if($result2['status_code']!=200){
                echo json_encode($result2);
                return;
            }
            $sql="SELECT Id_tag FROM tags WHERE tag LIKE ?";
            if($query = $enlace->prepare($sql)){
                $query->bind_param("s",$tag);
                $query->execute();
                $query->bind_result($id_tag);
                $query->fetch();
                $query->close();
            }else{
                echo json_encode(response(300,sqlError($sql,"s",$tag)));
                return;
            }
            array_push($tags_id,$id_tag);
        }
    }
    //Asigna las etiquetas al producto en la tabla map_tag
    $table3 = "map_tag";
    $params3 = array();
    $params3['Id_producto'] = $id_prod;
    $types3="ii";
    foreach($tags_id as &$id_tag){
        $params3['Id_tag'] = $id_tag;
        $result3 = dbInsert($table3,$types3,$params3);
        if($result3['status_code'] != 200){
            echo json_encode($result3);
            return;
        } 
    }  
    echo json_encode(response(200,array("Id_producto" => $id_prod)));
?>