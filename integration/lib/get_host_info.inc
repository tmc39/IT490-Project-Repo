<?php
/**
    @brief gets the currrent machine information and optionally any other task
    specific INI folder installed in the system path.
    Uses the default path of /var/system_ini/ unless $INIPATH is set

    @return parsed ini machine information

    UPDATED: This version always loads configuration files from: integration/config/
*/
function getHostInfo(array $extra = NULL)
{
    // Path to the config directory
    $configPath = __DIR__ . '/../config/';
    
    // Load main host configuration
    $machine = parse_ini_file($configPath . "host.ini",$process_sections=true);

    // Check if the main host.ini was loaded successfully
    if ($machine === false)
    {
        die ("Could not load host.ini from: " . $configPath . "host.ini\n");
    }
    
    // Load any addditional INI files if specified
    if ($extra != NULL)
    {
        foreach ($extra as $ini)
        {
            $parsed = parse_ini_file($configPath . $ini,true);
            if ($parsed)
            {
                $machine = array_merge($machine,$parsed);
            }
        }
    }
    return $machine;
}

?>

