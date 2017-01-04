<?php
  require 'conexionMySQL.php';
  class Monitorizacion
  {
    private $dispositivos;
    private $numeroDispositivos;
    private $conexion;

    function __construct()
    {
      $this->dispositivos = NULL;
      $this->numeroDispositivos = 0;
      $this->conexion = conexion();
      if (!$this->conexion->connect_error)
      {
        $this->obtenerDispositivos();
        $this->conexion->close();
        if (isset($this->dispositivos) && !is_null($this->dispositivos) && !empty($this->dispositivos) && $this->numeroDispositivos != 0)
        {
          for($i = 0; $i < $this->numeroDispositivos; $i++)
          {
            $this->obtenerPing($this->dispositivos[$i]);
            $this->obtenerSNMP($this->dispositivos[$i]);
            $this->actualizarRRD($this->dispositivos[$i]);
            $this->prediccionRRD($this->dispositivos[$i]);
          }
        }
      }
    }

    function obtenerDispositivos()
    {
      $consulta = "SELECT direccionIP, versionSNMP, comunidadSNMP FROM dispositivo WHERE monitorizacionActiva = '1' AND versionSNMP <> '0';";
      $resultadoQuery = $this->conexion->query($consulta);
      if($resultadoQuery)
      {
        while ($fila = $resultadoQuery->fetch_assoc())
        {
          $this->dispositivos[$this->numeroDispositivos]['direccionIP'] = $fila['direccionIP'];
          $this->dispositivos[$this->numeroDispositivos]['versionSNMP'] = $fila['versionSNMP'];
          $this->dispositivos[$this->numeroDispositivos]['comunidadSNMP'] = $fila['comunidadSNMP'];
          $this->dispositivos[$this->numeroDispositivos]['ping_recibidos'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['ping_errores'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['ping_duplicados'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['ping_perdidas'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['ping_promedio'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['cpu'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['ram'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['hdd'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['temp'] = 'u';
          $this->dispositivos[$this->numeroDispositivos]['volt'] = 'u';
          $this->numeroDispositivos++;
        }
      }
    }

    function porcentajeErrores($arreglo)
    {
      $banderaExistenErrores = false;
      $numeroErrores = 0;
      $indice = 0;
      for($i = 0; $i < count($arreglo); $i++)
      {
        $indice = $i;
        if ($arreglo[$i] === 'errors,')
        {
          $banderaExistenErrores = true;
          break;
        }
      }
      if ($banderaExistenErrores)
      {
        $numeroErrores = (int) $arreglo[$indice - 1];
      }
      return $numeroErrores * 10;
    }

    function porcentajeDuplicados($arreglo)
    {
      $banderaExistenDuplicados = false;
      $numeroDuplicados = 0;
      $indice = 0;
      for($i = 0; $i < count($arreglo); $i++)
      {
        $indice = $i;
        if ($arreglo[$i] === 'duplicates,')
        {
          $banderaExistenDuplicados = true;
          break;
        }
      }
      if ($banderaExistenDuplicados)
      {
        $numeroDuplicados = (int) $arreglo[$indice - 1];
      }
      return $numeroDuplicados * 10;
    }

    function obtenerPing(&$dispositivo)
    {
      $promedio = NULL;
      $exp = explode("/", $dispositivo['direccionIP']);
      $ip = $exp[0];
      $comando = "ping -c 10 -a ".$ip;
      $exec = exec ($comando, $arregloSalida, $valorRetorno);
      $datosPING = explode(" ", $arregloSalida[count($arregloSalida) - 2]);
      $tiemposPING = explode("/", $arregloSalida[count($arregloSalida) - 1]);
      if ($valorRetorno === 0)
      {
        $dispositivo['ping_promedio'] = (float) $tiemposPING[count($tiemposPING) - 3];
      }
      $dispositivo['ping_enviados'] = (int) $datosPING[0];
      $dispositivo['ping_recibidos'] = (int) $datosPING[3];
      $dispositivo['ping_errores'] = $this->porcentajeErrores($datosPING);
      $dispositivo['ping_duplicados'] = $this->porcentajeDuplicados($datosPING);
      $dispositivo['ping_perdidas'] = (int) $datosPING[count($datosPING) - 5];
    }

    function obtenerValor($arreglo)
    {
      $numeroEltos = count($arreglo);
      $valor = 'u';
      if ($numeroEltos > 0 && $arreglo[0] !== '' && !is_null($arreglo[0]))
      {
        $valor = (int) $arreglo[0];
      }
      return $valor;
    }

    function obtenerSNMP(&$dispositivo)
    {
      $version = NULL;
      $exp = explode("/", $dispositivo['direccionIP']);
      $ip = $exp[0];
      if($dispositivo['versionSNMP'] === '2')
      {
        $version = '2c';
      }
      else
      {
        $version = $dispositivo['versionSNMP'];
      }
      //Obtener 2021 para Health
      $comando2021 = "snmpwalk -v " . $version . " -c " . $dispositivo['comunidadSNMP'] . " " . $ip . " 1.3.6.1.4.1.2021 > snmp_health_$ip";
      $exec = exec ($comando2021, $arregloSalida, $valorRetorno1);
      //Obtener 2620 para Sensors
      $comando2620 = "snmpwalk -v " . $version . " -c " . $dispositivo['comunidadSNMP'] . " " . $ip . " 1.3.6.1.4.1.2620 > snmp_sensors_$ip";
      $exec = exec ($comando2620, $arregloSalida, $valorRetorno2);
      if ($valorRetorno1 === 0)
      {
        //CPU http://oidref.com/1.3.6.1.4.1.2021.11
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.50/||/ssCpuRawUser/{ print $4 }' snmp_health_$ip", $arregloSalida50, $valorRetorno);
        $ssCpuRawUser = $this->obtenerValor($arregloSalida50);
        if ($ssCpuRawUser === 'u')
        {
          $ssCpuRawUser = 0;
		    }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.51/||/ssCpuRawNice/{ print $4 }' snmp_health_$ip", $arregloSalida51, $valorRetorno);
        $ssCpuRawNice = $this->obtenerValor($arregloSalida51);
        if ($ssCpuRawNice === 'u')
        {
          $ssCpuRawNice = 0;
		    }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.52/||/ssCpuRawSystem/{ print $4 }' snmp_health_$ip", $arregloSalida52, $valorRetorno);
        $ssCpuRawSystem = $this->obtenerValor($arregloSalida52);
        if ($ssCpuRawSystem === 'u')
        {
          $ssCpuRawSystem = 0;
		    }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.53/||/ssCpuRawIdle/{ print $4 }' snmp_health_$ip", $arregloSalida53, $valorRetorno);
        $ssCpuRawIdle = $this->obtenerValor($arregloSalida53);
        if ($ssCpuRawIdle === 'u')
        {
          $ssCpuRawIdle = 0;
		    }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.54/||/ssCpuRawWait/{ print $4 }' snmp_health_$ip", $arregloSalida54, $valorRetorno);
        $ssCpuRawWait = $this->obtenerValor($arregloSalida54);
        if ($ssCpuRawWait === 'u')
        {
          $ssCpuRawWait = 0;
        }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.55/||/ssCpuRawKernel/{ print $4 }' snmp_health_$ip", $arregloSalida55, $valorRetorno);
        $ssCpuRawKernel = $this->obtenerValor($arregloSalida55);
        if ($ssCpuRawKernel === 'u')
        {
          $ssCpuRawKernel = 0;
        }
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.11.56/||/ssCpuRawInterrupt/{ print $4 }' snmp_health_$ip", $arregloSalida56, $valorRetorno);
        $ssCpuRawInterrupt = $this->obtenerValor($arregloSalida56);
        if ($ssCpuRawInterrupt === 'u')
        {
          $ssCpuRawInterrupt = 0;
        }
        $totalTicks = $ssCpuRawUser + $ssCpuRawNice + $ssCpuRawSystem + $ssCpuRawIdle + $ssCpuRawWait + $ssCpuRawKernel + $ssCpuRawInterrupt;
        if ($totalTicks !== 0 && $ssCpuRawIdle !== 0)
        {
          //$idle = ($ssCpuRawIdle / $totalTicks)*100;
          $dispositivo['cpu'] = ($ssCpuRawIdle / $totalTicks)*100;
        }
        //RAM http://oidref.com/1.3.6.1.4.1.2021.4
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.4.6/ || /memAvailReal/{ print $4 }' snmp_health_$ip", $arregloSalidaD, $valorRetorno);
        $memAvailReal = $this->obtenerValor($arregloSalidaD);
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.4.5/ || /memTotalReal/{ print $4 }' snmp_health_$ip", $arregloSalidaT, $valorRetorno);
        $memTotalReal = $this->obtenerValor($arregloSalidaT);
        if ($memTotalReal !== 'u' && $memAvailReal !== 'u' && $memTotalReal !== 0)
        {
          $dispositivo['ram'] = (int)(100 * ($memTotalReal - $memAvailReal) / $memTotalReal);
		    }
        //HDD http://oidref.com/1.3.6.1.4.1.2021.9.1
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.9.1.8.1/ || /dskUsed/{ print $4 }' snmp_health_$ip", $arregloSalidau, $valorRetorno);
        $dskUsed = $this->obtenerValor($arregloSalidau);
        $exec = exec ("awk '/iso.3.6.1.4.1.2021.9.1.6.1/ || /dskTotal/{ print $4 }' snmp_health_$ip", $arregloSalidad, $valorRetorno);
        $dskTotal = $this->obtenerValor($arregloSalidad);
        if ($dskTotal !== 'u' && $dskUsed !== 'u' && $dskTotal === 0)
        {
          $dispositivo['hdd'] = (int)(100 * $dskUsed / $dskTotal);
		    }
        $exec = exec ("rm -rf snmp_health_$ip", $arregloSalida, $valorRetorno);
      }
      if ($valorRetorno2 === 0)
      {
        // Sensores http://oidref.com/1.3.6.1.4.1.2620.1.6.7.8
        //TEMP
        $exec = exec ("awk '/iso.3.6.1.4.1.2620.1.6.7.8.1.1.3/ || /tempertureSensorValue/{ print $4 }' snmp_sensors_$ip", $arregloSalida1, $valorRetorno);
        $dispositivo['temp'] = $this->obtenerValor($arregloSalida1);
        //VOLT
        $exec = exec ("awk '/iso.3.6.1.4.1.2620.1.6.7.8.3.1.3/ || /voltageSensorValue/{ print $4 }' snmp_sensors_$ip", $arregloSalida2, $valorRetorno);
        $dispositivo['volt'] = $this->obtenerValor($arregloSalida2);
        $exec = exec ("rm -rf snmp_sensors_$ip", $arregloSalida, $valorRetorno);
	     }
    }

    function actualizarRRD($dispositivo)
    {
      $banderaActualizacion = false;
      $comando = "python upRRD.py ".$dispositivo['direccionIP']." ".$dispositivo['cpu']." ".$dispositivo['ram']." ".$dispositivo['hdd']." ".$dispositivo['temp']." ".$dispositivo['volt']." ".$dispositivo['ping_promedio'];
      $exec = exec ($comando, $arregloSalida, $valorRetorno);
      if ($valorRetorno === 0)
      {
        $banderaActualizacion = true;
      }
      //return $banderaActualizacion;
    }

    function prediccionRRD($dispositivo)
    {
      $banderaPrediccion = false;
      $comando = "python predicciones.py ".$dispositivo['direccionIP'];
      $exec = exec ($comando, $arregloSalida, $valorRetorno);
      if ($valorRetorno === 0)
      {
        $banderaPrediccion = true;
      }
      //return $banderaPrediccion;
    }
  }
  $monitor = new Monitorizacion();
  unset($monitor);
?>
