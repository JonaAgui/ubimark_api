
<?PHP
    include_once("conectar.php");
    include("funciones.php");
    //Funcion para comprobar si existe una etiqueta
    function tag_exist($tag,$tags,$enlace){
        $c = 0;
        $tag="%".$tag."%";
        $params = "s";
        $sql="SELECT Id_tag FROM tags WHERE tag LIKE ?";
        if(count($tags)>0){
            $sql.=" AND Id_tag NOT IN(?,";
            $params=$params."i";
            for($i=0;$i<(count($tags)-1);$i++){
                $sql = $sql."?,";
                $params=$params."i";
            }
            $sql=substr($sql,0,strlen($sql)-1).")";
        }
        $a_params=array();
        $a_params[] = & $params;
        $a_params[] = & $tag;
        for($i = 0; $i < count($tags); $i++) {
            $a_params[] = & $tags[$i];
        }
        if($query=$enlace->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $query->bind_result($c);
            $query->fetch();
            $query->close();
        } 
        //echo $tag." ".$c."<br>";
        return $c;
    }

    //Funcion que obtiene los productos pertenecientes a $a\$e 
    function multiTag2Product($a,$e,$enlace){
        //Define la cantidad de elementos del conjunto $a y genera la instruccion sql
        $n=count($a);
        
        $sql="SELECT Id_producto FROM map_tag where Id_tag IN (?,";
        $params="s";
        for($i=0;$i<($n-1);$i++){
            $sql = $sql."?,";
            $params=$params."s";
        }
        $sql=substr($sql,0,strlen($sql)-1).")";
        //Define la cantidad de elementos del conjunto $e y genera la instruccion sql
        if(count($e)>0){
            $sql.=" AND Id_producto NOT IN(?,";
            $params=$params."s";
            for($i=0;$i<(count($e)-1);$i++){
                $sql = $sql."?,";
                $params=$params."s";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1).") GROUP BY Id_producto HAVING COUNT(Id_producto)=".$n;
        //crea el arreglo con $aU$b y el string correspondiente a la cantidad de parametros esperados
        $a_params=array();
        $a_params[] = & $params;
        if($n>1){
            for($i = 0; $i < $n; $i++) {
                $a_params[] = & $a[$i];
            }
        }else{
            $a_params[] = & $a[0];
        }
        
        for($i = 0; $i < count($e); $i++) {
          $a_params[] = & $e[$i];
        }
        //ejecuta la consulta sql preparada y devuelve $a\$b
        if($query=$enlace->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $res=$query->get_result();
            $query->close();
        }
        return $res;
        
    }


    function buscar($token,$results = array()){
        $enlace = getDBConnection();
        $arr = preg_split("/[\s,]+/",$token);

        //Se realiza la primera busqueda en el nombre del producto y se almacenan los resultados
        $prod="%".$token."%";
        if($query=$enlace->prepare("SELECT Id_producto FROM productos WHERE nombre_producto LIKE ?")){
            $query->bind_param("s",$prod);
            $query->execute();
            $res=$query->get_result();
            $query->close();
        }
        while($row=$res->fetch_Assoc()){
            array_push($results,$row['Id_producto']);
        }

        $tags= array();
        //Valida si una de las etiquetas encontradas en la busqueda existe de no ser el caso realiza transformaciones y compara nuevamente
        foreach($arr as &$val){
            $val=strtoupper($val);
            if (($id_tag=tag_exist($val,$tags,$enlace))> 0){
                array_push($tags,$id_tag);
            }else{
                if(strrpos($val,'ES')==strlen($val)-2&&strrpos($val,'ES')>0){
                    $temp=substr($val,0,strlen($val)-2);
                    if(strrpos($temp,'C')==strlen($temp)-1){
                        $temp=substr($temp,0,strlen($temp)-1)."Z";
                    }
                    if(($id_tag=tag_exist($temp,$tags,$enlace))>0){
                        array_push($tags,$id_tag);
                    }
                }else if(strrpos($val,'IS')==strlen($val)-2&&strrpos($val,'IS')>0){
                    $temp=substr($val,0,strlen($val)-2)."Y";
                    if(($id_tag=tag_exist($temp,$tags,$enlace))>0){
                        array_push($tags,$id_tag);
                    }
                }else if(strrpos($val,'S')==strlen($val)-1){
                    $temp=substr($val,0,strlen($val)-1);
                    if(($id_tag=tag_exist($temp,$tags,$enlace))>0){
                        array_push($tags,$id_tag);
                    }else{
                        $val=$temp;
                    }
                }
                if(strrpos($val,"A")==strlen($val)-1||strrpos($val,"O")==strlen($val)-1){
                    $temp=substr($val,0,strlen($val)-1);
                    if(($id_tag=tag_exist($temp,$tags,$enlace))>0){
                        array_push($tags,$id_tag);
                    }
                }
            }
        }
        // se establece los datos para realizar la combinatoria sin repeticion del conjunto de etiquetas y se genera el arreglo de resultados que contendra al conjunto de elementos a excluir
        $n=count($tags)-1;
        $m=count($tags);
        
        //Se comprueba que la busqueda contenga al menos una etiqueta valida
        if(count($tags)>0){
            //Se realiza la primera busqueda por etiquetas donde el producto coincida con cada etiqueta en $tags y se almacenan los resultados
            $temp=multiTag2Product($tags,$results,$enlace);
            while($row=$temp->fetch_Assoc()){
                array_push($results,$row['Id_producto']);
            }
            //Comienza la combinatoria de ser necesario solo si hay por lo menos 3 etiquetas validas
            while($n>1){
                $tags2use=array(); //etiquetas por combinacion
                $count=array(); //contador
                $is=range(0,$n-1); //indices de 0 a $n-1
                for($i=0;$i<$n;$i++){
                    array_push($count,0); //se establece el contador en 0 para cada indice
                }
                $i=count($is)-1; //apuntador al ultimo indice
                while($is[0]<$m-$n){ //se comprueba que la posicion del primer indice
                    if($is[$i]<=$m-($m-($i+($m-$n)))){ //se comprueba que el apuntador actual no haya alcanzado el limite
                        foreach($is as &$temp){ 
                            array_push($tags2use, $tags[$temp]);//se toman los indices para obtener una combinacion de $n etiquetas
                        } 
                        //Se realiza la busqueda de productos que coinciden con n etiquetas
                        $r=multiTag2Product($tags2use,$results,$enlace);
                        while($row=$r->fetch_Assoc()){
                            array_push($results,$row['Id_producto']);
                        }
                        $tags2use=array(); //Se espera la siguiente combinacion
                        $is[$i]++; 
                        $count[$i]++;                
                    }else{ //En caso de que el apuntador actual alcanzara el limite
                        $is[$i]--;
                        $count[$i]--;
                        if($count[$i]>1){ 
                            $is[$i]-=($count[$i]-1);
                            $count[$i]=0;
                            for($j=$i;$j<count($count)-1;$j++){
                                $is[$j]-=($count[$i]-1);
                                $count[$j]=0;
                            }
                            $i--;
                            $is[$i]++;
                            $count[$i]++;
                            $i=count($is)-1;
                        }else{
                            $i--;
                            $is[$i]++;
                            $count[$i]++;
                            if(!$is[0]<$m-$n){
                                foreach($is as &$temp){
                                    if(isset($tags[$temp])){
                                        array_push($tags2use, $tags[$temp]);
                                    }
                                }               
                                $r=multiTag2Product($tags2use,$results,$enlace);
                                while($row=$r->fetch_Assoc()){
                                    array_push($results,$row['Id_producto']);
                                }
                                $tags2use=array();
                            }
                        }
                    }
                }
                $n--;
                
            }
            //Ultima busqueda por etiquetas en caso de que se encontrara por lo menos dos etiquetas validas busca coincidencia con al menos 1 etiqueta
            if($n===1){
                foreach($tags as &$temp){
                    $t=array();
                    array_push($t,$temp);
                    $r=multiTag2Product($t,$results,$enlace);
                    while($row=$r->fetch_Assoc()){
                        array_push($results,$row['Id_producto']);
                    }
                }
            }
        }
        return $results;
    }
    ?>