<?php
  function conexion()
  {
    $host = "localhost";
    $usuario = "root";
    $clave = "root";
    $basededatos = "AdministracionServiciosRed";
    $mysqli = new mysqli($host, $usuario, $clave, $basededatos);
    /*
    if ($mysqli->connect_error)
    {
      die('Error de Conexion (' . $mysqli->connect_errno . ') '. $mysqli->connect_error . "\n");
    }
    else
    {
      echo "Conexion Satisfactoria\n";
    }
    */
    return $mysqli;
  }
?>
