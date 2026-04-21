<?php
$path = '/usr/local/lib/system_libs';

    if (file_exists(__DIR__ . '/../config/local.ini'))
    {
        $parse = parse_ini_file(__DIR__ . '/../config/local.ini',true);
        
        if (isset($parse['paths']) && isset($parse['paths']['libraries']))
        {
            $path = $parse['paths']['libraries'];
        }
        else
        {
           trigger_error("local.ini missing paths field\n",E_USER_NOTICE); 
        }
    }
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
?>
