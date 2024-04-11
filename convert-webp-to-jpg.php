<?php


$json = new stdClass();
$json->error = '';
$json->success = 0;
$json->images = array();


$json->success = processFiles($json)?1:0;
print json_encode($json);


function processFiles($json){
    if(empty($_FILES) || empty($_FILES['files-to-convert'])){
        return false;
    }

    $filesProcessed = 0;

    foreach ($_FILES['files-to-convert']['tmp_name'] as $k => $v){

        if(empty($v)){
            continue;
        }

        try{
            $check = getimagesize($v);
            if($check === false) {
                $json->error = "File is not an image.";
                return false;
            }

            $mime = mime_content_type($v);
            if($mime !== 'image/webp'){
                $json->error = "File is not WebP image.";
                return false;
            }

            $im = imagecreatefromwebp($v);

            // Convert it to a jpeg file with 100% quality

            $imgageJs = new stdClass();
            $path_parts = pathinfo($_FILES['files-to-convert']['name'][$k]);
            $imgageJs->name = $path_parts['filename'].'.jpg';
            $imgageJs->mime = 'data:image/jpg;base64';

            ob_start();
            imagejpeg($im, null, 100);
            imagedestroy($im);
            $imgageJs->base64 =  $imgageJs->mime.','.base64_encode(ob_get_contents());
            ob_end_clean();

            $json->images[] = $imgageJs;
        }
        catch (Exception $e) {

        }

    }

    if(!empty($json->images)){
        return true;
    }


    $json->error = "No files process";
    return false;
}