<?php
  require "Administrador.php";
  if (isset($_POST) && !empty($_POST))
  {
    if (isset($_POST['operacion']) && !empty($_POST['operacion']) && !is_null($_POST['operacion']))
    {
      if ($_POST['operacion'] === 'eliminar')
      {
        $administrador = new Administrador($_POST['email'], NULL, NULL, $_POST['operacion']);
      }
      if ($_POST['operacion'] === 'insertar' || $_POST['operacion'] === 'actualizar')
      {
        $administrador = new Administrador($_POST['email'], $_POST['pass'], $_POST['logueo'], $_POST['operacion']);
      }
    }
  }
  if (count($argv) === 5)
  {
    $administrador = new Administrador($argv[1], $argv[2], $argv[3], $argv[4]);
  }
  if (count($argv) === 2)
  {
    $administrador = new Administrador($argv[1], NULL, NULL, 'eliminar');
  }
  if (isset($administrador) && !empty($administrador) && !is_null($administrador))
  {
    unset($administrador);
    return 0;
  }
  else
  {
    return -1;
  }
?>
