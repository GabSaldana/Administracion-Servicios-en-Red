<?php
  require "BitacoraSucesos.php";
  if (count($argv) === 6)
  {
    $bitacora = new BitacoraSucesos($argv[1], $argv[2], $argv[3], $argv[4], '', '', $argv[5], 'insertar');
  }
  else if (count($argv) === 7)
  {
    $bitacora = new BitacoraSucesos($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], 0, 'eliminar');
  }
  if (isset($bitacora) && !empty($bitacora) && !is_null($bitacora))
  {
    unset($bitacora);
    return 0;
  }
  else
  {
    return -1;
  }
?>
