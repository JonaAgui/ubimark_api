<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $path = basename($_POST['path']);
    $sess = $_POST['session'];
    $band = $sess == 1;
    $sess_required['required'] = file("required.ubi",FILE_IGNORE_NEW_LINES |FILE_SKIP_EMPTY_LINES);
    $sess_required['no_required'] = file("norequired.ubi",FILE_IGNORE_NEW_LINES |FILE_SKIP_EMPTY_LINES);
    if(!notNull($path)){
        echo "path null";
        return;
        $path="/";
    }
    if(strlen($_POST['path'])>=9){
        $i = strcmp("intranet/",substr($_POST['path'],0,9));
        if($i == 0){
            $path="/";
        }
    }
    

    if(in_array($path, $sess_required['required'])){
        if($band){
            echo json_encode(response(107,$path));
        }else{
            echo json_encode(response(106,$path));
        }
    }else if(in_array($path, $sess_required['no_required'])){
        echo json_encode(response(107,$path));
    }else{
        echo json_encode(response(108,$path));
    }
?>