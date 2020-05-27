<?php
define( 'MYSQL_HOST', 'localhost' );
define( 'MYSQL_PORT', '3306' );
define( 'MYSQL_USER', 'root' );
define( 'MYSQL_PASSWORD', '' );
define( 'MYSQL_DB_NAME', 'cent' );

try
{
    $PDO = new PDO( 'mysql:host=' . MYSQL_HOST . ';port='.MYSQL_PORT.';dbname=' . MYSQL_DB_NAME, MYSQL_USER, MYSQL_PASSWORD );
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}

?>