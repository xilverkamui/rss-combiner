<?php

function saveUrl ($url, $file_name) {
    
    $result = false;
    // Use basename() function to return the base name of file 
    if (!$file_name)
        $file_name = basename($url); 
    //echo $file_name;

    // Use file_get_contents() function to get the file 
    // from url and use file_put_contents() function to 
    // save the file by using base name 
    //if (file_put_contents($file_name, file_get_contents($url))) {
    if (file_put_contents($file_name, url_get_contents($url))) { 
        $result = true;
    }

    return $result;
}

function url_get_contents ($url) {
    if (is_file($url)) {
        echo 'File ketemu';
        ob_start();
        include $url;
        return ob_get_clean();
    }
    else {
        return file_get_contents($url); 
    }
    //echo 'URL Not Found: ' . $url;
    //return false;
}
    
?>