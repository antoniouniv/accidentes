<?php
require(dirname(__FILE__) . '/wp-blog-header.php');

ini_set('max_execution_time', 600);
ini_set('memory_limit', '1024M');

set_time_limit(0);
$date = new Datetime();
echo "Empieza proceso ..." . $date->format('d-m-Y H:i:s') . "\n";
echo "<br>";

echo 'Las variables globales son: ';
echo "<br>";
echo 'TC ' . TC;
echo "<br>";
echo 'END_CONFIG_FOLDER ' . END_CONFIG_FOLDER;
echo "<br>";
echo 'END_FOLDER ' . END_FOLDER;
echo "<br>";
echo 'INI_FOLDER ' . INI_FOLDER;
echo "<br>";
echo 'END_MEDIA_FOLDER ' . END_MEDIA_FOLDER;
echo "<br>";
echo 'ABSPATH ' . ABSPATH;
echo "<br>";
echo 'GLOB_BRACE ' . GLOB_BRACE;
echo "<br>";
echo 'RENAME_UPLOADS ' . RENAME_UPLOADS;
echo "<br>";
echo 'PUBLICAR ' . PUBLICAR;
echo "<br>";

$page_id = $_GET['id'];

if ($page_id != null) {
    echo "Se publicará la página " . $page_id;
    echo "<br>";
    copySingleFolder($page_id);
}else{
    echo "Se publicarán todas las páginas";
    echo "<br>";
    copyPrevisFolder();
}

renameUrlsFile(ABSPATH . str_replace('app/pages/', '', END_CONFIG_FOLDER). "index.html");
changeConfigOption();
finalPublish();

echo "\n" . "----------" . "\n";
$final_date = new Datetime();
echo "Proceso finalizado correctamente " . $final_date->format('d-m-Y H:i:s');

function copySingleFolder($page_id)
{
    echo "Publicado final de una página"."<br>";

    $permalink = get_page_link($page_id);
    if (basename($permalink) == "index"){
        $ini_file_name = TC . basename($permalink) . '.html';
        $end_file_name = str_replace('app/pages/', '', END_CONFIG_FOLDER) . basename($permalink) . '.html';
    }else {
        $ini_file_name = TC . 'app/pages/' . basename($permalink) . '.html';
        $end_file_name = END_CONFIG_FOLDER . basename($permalink) . '.html';
    }

    echo "Se ejecutará el siguiente comando: " . 'cp -Rp ' . $ini_file_name . ' ' . $end_file_name. "<br>";
    echo('---- <br>');
    echo('---- <br>');
    echo($ini_file_name ."<br>");
    echo($end_file_name. "<br>");
    echo('----<br>');
    echo('----<br>');
    //shell_exec('cp -Rp ' . $ini_file_name . ' ' . $end_file_name);
    copyRecursively($ini_file_name, $end_file_name);
    moveAndRenameUploads();
}

function copyPrevisFolder()
{
    if (file_exists(END_FOLDER)) {
        echo "Se ejecutará el siguiente comando: "  . 'rm -rf ' . END_FOLDER.'/*';
        echo "<br>";
        //shell_exec('rm -rf ' . END_FOLDER.'/*');
        deleteRecursively(END_FOLDER);
    }else{
        if ( wp_mkdir_p( END_FOLDER ) ) {
            chmod( END_FOLDER, 0755 );
            echo 'No existe el directorio '. END_FOLDER . '. Lo creo y le doy los permisos 0755'."<br>";
        }else{
            echo 'No se ha podido crear el directorio ' . END_FOLDER."<br>";
        }
    }

    echo "Se ejecutará el siguiente comando: "  . 'cp -Rp ' . INI_FOLDER . '/* ' . END_FOLDER.'/';
    echo "<br>";
    //shell_exec('cp -Rp ' . INI_FOLDER . '/* ' . END_FOLDER.'/');
    copyRecursively(INI_FOLDER, END_FOLDER);
    moveAndRenameUploads();
}

//Meter en nuestra carpeta final la carpeta Uploads dentro de 'es' y llamarla media
function moveAndRenameUploads()
{
    $upload_path = 'wp-content/uploads/';

    if ( wp_mkdir_p( END_MEDIA_FOLDER ) ) {
        chmod( END_MEDIA_FOLDER, 0755 );
        echo 'No existe el direcotrio '. END_MEDIA_FOLDER . '. Lo creo y le doy los permisos 0755'."<br>";
    }else{
        echo 'No se ha podido crear el directorio ' . END_MEDIA_FOLDER."<br>";
    }

    echo "Se ejecutará el siguiente comando: "  . 'cp -RP ' . $upload_path . ' ' . END_MEDIA_FOLDER.'/'."<br>";
    //shell_exec('cp -RP ' . $upload_path . ' ' . END_MEDIA_FOLDER.'/');
    copyRecursively($upload_path, END_MEDIA_FOLDER);
    $final_folder = ABSPATH . END_FOLDER;
    renameUrls($final_folder);
}

//renombrar urls de imagenes a media/....
function renameUrls($folder)
{
    $files = glob($folder . '/*.*', GLOB_BRACE);
    foreach ($files as $path_to_file) {
        $file_contents = file_get_contents($path_to_file);
        $upload_dir = wp_upload_dir();
        $upload_url = $upload_dir['baseurl'];
        $file_contents = str_replace($upload_url, "./" . RENAME_UPLOADS, $file_contents);

        file_put_contents($path_to_file, $file_contents);
    }
    //Sacamos directorios y subdirectorios
    $dirs = glob($folder . "/*", GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        renameUrls($dir);
    }
}

//renombrar dominio en urls de una archivo en concreto
function renameUrlsFile($file)
{
    $file_contents = file_get_contents($file);
    $file_contents = str_replace("/".INI_FOLDER."/", "/".END_FOLDER."/", $file_contents);
    file_put_contents($file, $file_contents);
}


//Change config.json var real a true
function changeConfigOption()
{
    $config_file = END_CONFIG_FOLDER . 'config.json';
    $jsonString = file_get_contents($config_file);
    if ($jsonString) {
        $data = json_decode($jsonString, true);
        $data['data']['real'] = true;
        $newJsonString = json_encode($data);
        file_put_contents($config_file, $newJsonString);
    }
}

//Llamar al script de publicación final
function finalPublish()
{
    //shell_exec("curl " . get_site_url().'/'.PUBLICAR);

    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => get_site_url() . '/' . PUBLICAR,
        CURLOPT_USERAGENT => 'cURL Request'
    ]);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);
}

function copyRecursively($fuente, $destino)
{
    if(is_dir($fuente))
    {
        $dir=opendir($fuente);
        while($archivo=readdir($dir))
        {
            if($archivo!="." && $archivo!="..")
            {
                if(is_dir($fuente."/".$archivo))
                {
                    if(!is_dir($destino."/".$archivo))
                    {
                        mkdir($destino."/".$archivo);
                    }
                    copyRecursively($fuente."/".$archivo, $destino."/".$archivo);
                }
                else
                {
                    copy($fuente."/".$archivo, $destino."/".$archivo);
                    $dt = filemtime($fuente."/".$archivo);
                    if ($dt) {
                        touch($destino."/".$archivo, $dt);
                    }
                }
            }
        }
        closedir($dir);
    }
    else
    {
        copy($fuente, $destino);
        $dt = filemtime($fuente);
        if ($dt) {
            touch($destino, $dt);
        }
    }
}

function deleteRecursively($dir) { 
    if (is_dir($dir)) { 
      $objects = scandir($dir);
      foreach ($objects as $object) { 
        if ($object != "." && $object != "..") { 
          if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
            deleteRecursively($dir. DIRECTORY_SEPARATOR .$object);
          else
            unlink($dir. DIRECTORY_SEPARATOR .$object); 
        } 
      }
      rmdir($dir); 
    } 
  }

?>