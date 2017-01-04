<?php
  require "permisosAdministracion.php";
  if (isset($_POST) && !empty($_POST))
  {
    if (isset($_POST['operacion']) && !empty($_POST['operacion']) && !is_null($_POST['operacion']))
    {
      if ($_POST['operacion'] === 'eliminar')
      {
        $permisos = new permisosAdministracion($_POST['email'], $_POST['dirIP'], NULL, $_POST['operacion']);
      }
      if ($_POST['operacion'] === 'insertar' || $_POST['operacion'] === 'actualizar')
      {
        $permisos = new permisosAdministracion($_POST['email'], $_POST['dirIP'], $_POST['permiso'], $_POST['operacion']);
      }
    }
  }
  if (count($argv) === 5)
  {
    $permisos = new permisosAdministracion($argv[1], $argv[2], $argv[3], $argv[4]);
  }
  else if (count($argv) === 3)
  {
    $permisos = new permisosAdministracion($argv[1], $argv[2], NULL, 'eliminar');
  }
  if (isset($permisos) && !empty($permisos) && !is_null($permisos))
  {
    unset($permisos);
    return 0;
  }
  else
  {
    return -1;
  }
?>
