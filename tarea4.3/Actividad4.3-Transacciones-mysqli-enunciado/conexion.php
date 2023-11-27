
<?php
const SETTINGS_INI = 'db_settings.ini';
/**
 * Summary of getConnection
 * Crea un objeto mysqli. Si ocurre algún error leyendo el fichero de configuración lanza una excepción.
 * @return mysqli|null un objeto mysqli si ha habido éxito creando la conexión, null en caso contrario
 */

function getConnection(): mysqli
{

    if (!$settings = parse_ini_file(SETTINGS_INI, true)) throw new Exception("ERROR: Unable to open" . SETTINGS_INI);

    $con = null;
    $host = $settings['database']['host'];
    $db = $settings['database']['schema'];
    $user = $settings['database']['username'];
    $pass = $settings['database']['password'];
    $port = $settings['database']['port'];

    //$con = new mysqli($host, $user, $pass, $db, $port);
    //  return $con;
    //}



    try {
        $con = new mysqli($host, $user, $pass, $db, $port);
    } catch (mysqli_sql_exception $ex) {
        echo "Error de conexión: mensaje: " . $ex->getMessage();
    }
    return $con;
}
