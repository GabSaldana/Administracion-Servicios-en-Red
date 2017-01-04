<?php
  require "Restricciones.php";
  if (isset($_POST) && !empty($_POST))
  {
    if (isset($_POST['operacion']) && !empty($_POST['operacion']) && !is_null($_POST['operacion']))
    {
      if ($_POST['operacion'] === 'eliminar')
      {
        $restricciones = new Restricciones($_POST['dirIP'], $_POST['campoMonit'], $_POST['valorAntic'], $_POST['tipoValAntic'], NULL, $_POST['operacion']);
      }
      if ($_POST['operacion'] === 'insertar' || $_POST['operacion'] === 'actualizar')
      {
        $restricciones = new Restricciones($_POST['dirIP'], $_POST['campoMonit'], $_POST['valorAntic'], $_POST['tipoValAntic'], $_POST['alertaActiv'], $_POST['operacion']);
      }
    }
  }
  if (count($argv) === 7)
  {
    $restricciones = new Restricciones($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6]);
  }
  else if (count($argv) === 5)
  {
    $restricciones = new Restricciones($argv[1], $argv[2], $argv[3], $argv[4], NULL, 'eliminar');
  }
  if (isset($restricciones) && !empty($restricciones) && !is_null($restricciones))
  {
    unset($restricciones);
    return 0;
  }
  else
  {
    return -1;
  }
?>
