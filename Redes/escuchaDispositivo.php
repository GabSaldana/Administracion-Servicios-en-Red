<?php
  require "Dispositivo.php";
  if (isset($_POST) && !empty($_POST))
  {
    if (isset($_POST['operacion']) && !empty($_POST['operacion']) && !is_null($_POST['operacion']))
    {
      if ($_POST['operacion'] === 'eliminar')
      {
        $dispositivo = new Dispositivo($_POST['email'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $_POST['operacion']);
      }
      if ($_POST['operacion'] === 'insertar' || $_POST['operacion'] === 'actualizar')
      {
        $dispositivo = new Dispositivo($_POST['dirIP'], $_POST['monitActiv'], $_POST['tipoDisp'], $_POST['verSNMP'], $_POST['comSNMP'], $_POST['minCPU'], $_POST['maxCPU'], $_POST['avgCPU'], $_POST['minRAM'], $_POST['maxRAM'], $_POST['avgRAM'], $_POST['minHDD'], $_POST['maxHDD'], $_POST['avgHDD'], $_POST['minTEMP'], $_POST['maxTEMP'], $_POST['avgTEMP'], $_POST['minVOLT'], $_POST['maxVOLT'], $_POST['avgVOLT'], $_POST['ping_promedio'], $_POST['operacion']);
      }
    }
  }
  if (count($argv) === 23)
  {
    $dispositivo = new Dispositivo($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10], $argv[11], $argv[12], $argv[13], $argv[14], $argv[15], $argv[16], $argv[17], $argv[18], $argv[19], $argv[20], $argv[21], $argv[22]);
  }
  else if (count($argv) === 2)
  {
    $dispositivo = new Dispositivo($argv[1], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'eliminar');
  }
  if (isset($dispositivo) && !empty($dispositivo) && !is_null($dispositivo))
  {
    unset($dispositivo);
    return 0;
  }
  else
  {
    return -1;
  }
?>
