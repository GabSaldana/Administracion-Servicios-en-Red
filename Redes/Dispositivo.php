<?php
  require 'conexionMySQL.php';
  class Dispositivo
  {
    private $ip;
    private $msk;
  	private $direccionIP;
  	private $fechaRegistroHost;
  	private $monitorizacionActiva;
  	private $tipoDispositivo;
    private $versionSNMP;
    private $comunidadSNMP;
  	private $ruta;
  	private $min_cpu;
  	private $max_cpu;
  	private $avg_cpu;
  	private $min_ram;
  	private $max_ram;
  	private $avg_ram;
  	private $min_hdd;
  	private $max_hdd;
  	private $avg_hdd;
  	private $min_temp;
  	private $max_temp;
  	private $avg_temp;
  	private $min_volt;
  	private $max_volt;
  	private $avg_volt;
    private $ping_promedio;
    private $conexion;
    private $raiz = "/home/vsalmean/Redes/Hosts/";
    //private $raiz = '/opt/Redes/Hosts/';

  	function __construct ($dirIP, $monitActiv, $tipoDisp, $verSNMP, $comSNMP, $minCPU, $maxCPU, $avgCPU, $minRAM, $maxRAM, $avgRAM, $minHDD, $maxHDD, $avgHDD, $minTEMP, $maxTEMP, $avgTEMP, $minVOLT, $maxVOLT, $avgVOLT, $ping, $oper)
  	{
      $banderaDispositivo = false;
      $cidr = explode("/", $dirIP);
      $this->ip = $cidr[0];
      $this->msk = (int)$cidr[1];
  		$this->direccionIP = $this->ip.'/'.$this->msk;
      $this->fechaRegistroHost = date("Y-m-d", time());
      $this->monitorizacionActiva = $monitActiv;
      $this->tipoDispositivo = $tipoDisp;
      $this->versionSNMP = $verSNMP;
      $this->comunidadSNMP = $comSNMP;
      $cadena = str_replace('.','_',$this->ip);
      $this->ruta = $this->raiz.$cadena.'_'.$this->msk;
      $this->min_cpu = $minCPU;
      $this->max_cpu = $maxCPU;
      $this->avg_cpu = $avgCPU;
      $this->min_ram = $minRAM;
      $this->max_ram = $maxRAM;
      $this->avg_ram = $avgRAM;
      $this->min_hdd = $minHDD;
      $this->max_hdd = $maxHDD;
      $this->avg_hdd = $avgHDD;
      $this->min_temp = $minTEMP;
      $this->max_temp = $maxTEMP;
      $this->avg_temp = $avgTEMP;
      $this->min_volt = $minVOLT;
      $this->max_volt = $maxVOLT;
      $this->avg_volt = $maxVOLT;
      $this->ping_promedio = $ping;
      $this->conexion = conexion();
      $arregloErrores = $this->validarCamposDispositivo($oper);
      var_dump($arregloErrores);
      if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
      {
        if ($oper === 'insertar')
        {
          if (!$this->comprobarExistenciaDispositivo())
          {
            if ($this->insertarDispositivo())
            {
              $banderaDispositivo = $this->crearDirectorioDispositivo();
            }
          }
    		}
    		else if ($oper === 'actualizar')
        {
          if ($this->comprobarExistenciaDispositivo())
          {
            $banderaDispositivo = $this->actualizarDispositivo();
          }
    		}
    		else if ($oper === 'eliminar')
        {
          if ($this->comprobarExistenciaDispositivo())
          {
            if ($this->eliminarDispositivo())
            {
              $banderaDispositivo = $this->eliminarDirectorioDispositivo();
            }
          }
    	   }
      }
      var_dump($this);
      $this->conexion->close();
      //return $banderaDispositivo;
  	}

    private function crearDirectorioDispositivo ()
  	{
  		$banderaDirectorio = false;
      $cadena = str_replace ('.','_',$this->ip);
      $crearDir = "mkdir ".$this->ruta.$cadena."_".$this->msk;
  		$exec = exec ($crearDir, $arregloSalida, $valorRetorno);
  		//Si ya existe el directorio
  		if ($valorRetorno === 1)
  		{
        $this->eliminarDirectorioDispositivo();
        $banderaDirectorio = $this->crearDirectorioDispositivo();
  		}
  		//Si se creo satisfactoriamente el directorio
  		else if ($valorRetorno === 0)
  		{
        $exec = exec ("python crRRD.py $this->direccionIP", $arregloSalida, $valorRetorno);
  		}
  		return $banderaDirectorio;
    }

  	private function eliminarDirectorioDispositivo ()
  	{
  		$banderaDirectorio = false;
      $cadena = str_replace ('.','_',$this->ip);
      $elimDir = "rm -rf ".$this->ruta.$cadena."_".$this->msk;
  		$exec = exec ($elimDir, $arregloSalida, $valorRetorno);
  		//Si se creo exitosamente el directorio
  		if ($valorRetorno === 0)
  		{
  			$banderaDirectorio = true;
  		}
  		return $banderaDirectorio;
  	}

  	private function comprobarExistenciaDispositivo()
    {
      $numeroFilasExistentes = 0;
      $existeDispositivo = false;
      $consulta = "SELECT * FROM dispositivo WHERE direccionIP = '" . $this->direccionIP . "';";
      $resultadoQuery = $this->conexion->query($consulta);
      if($resultadoQuery)
      {
        while ($fila = $resultadoQuery->fetch_assoc())
        {
          $numeroFilasExistentes++;
        }
      }
      if ($numeroFilasExistentes > 0)
      {
        $existeDispositivo = true;
      }
      return $existeDispositivo;
    }

    private function insertarDispositivo ()
    {
      $altaExitosa = false;
      $consulta = "CALL insertarDispositivo ('" . $this->direccionIP . "','" . $this->fechaRegistroHost . "','" . $this->monitorizacionActiva . "','" . $this->tipoDispositivo . "','" . $this->versionSNMP . "','" . $this->comunidadSNMP . "','" . $this->ruta ."'," . $this->min_cpu . "," . $this->max_cpu . "," . $this->avg_cpu . "," . $this->min_ram . "," . $this->max_ram . "," . $this->avg_ram . "," . $this->min_hdd . "," . $this->max_hdd . "," . $this->avg_hdd . "," . $this->min_temp . "," . $this->max_temp . "," . $this->avg_temp . "," . $this->min_volt . "," . $this->max_volt . "," . $this->avg_volt . "," . $this->ping_promedio . ")";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $altaExitosa = true;
      }
      return $altaExitosa;
    }

    private function actualizarDispositivo ()
    {
      $actualizacionExitosa = false;
      $consulta = "CALL actualizarDispositivo ('" . $this->direccionIP . "','" . $this->monitorizacionActiva . "','" . $this->tipoDispositivo . "','" . $this->versionSNMP . "','" . $this->comunidadSNMP . "'," . $this->min_cpu . "," . $this->max_cpu . "," . $this->avg_cpu . "," . $this->min_ram . "," . $this->max_ram . "," . $this->avg_ram . "," . $this->min_hdd . "," . $this->max_hdd . "," . $this->avg_hdd . "," . $this->min_temp . "," . $this->max_temp . "," . $this->avg_temp . "," . $this->min_volt . "," . $this->max_volt . "," . $this->avg_volt . "," . $this->ping_promedio . ")";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $actualizacionExitosa = true;
      }
      return $actualizacionExitosa;
    }

    private function eliminarDispositivo ()
    {
      $bajaExitosa = false;
      $consulta = "CALL eliminarDispositivo ('" . $this->direccionIP . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $bajaExitosa = true;
      }
      return $bajaExitosa;
    }

    private function validarCamposDispositivo ($oper)
    {
      $arregloErrores = array('direccionIP' => NULL, 'fechaRegistroHost' => NULL, 'monitorizacionActiva' => NULL, 'tipoDispositivo' => NULL, 'versionSNMP' => NULL, 'comunidadSNMP' => NULL, 'min_cpu' => NULL,'max_cpu' => NULL, 'avg_cpu' => NULL, 'min_ram' => NULL, 'max_ram' => NULL, 'avg_ram' => NULL, 'min_hdd' => NULL, 'max_hdd' => NULL, 'avg_hdd' => NULL, 'min_temp' => NULL, 'max_temp' => NULL, 'avg_temp' => NULL, 'min_volt' => NULL, 'max_volt' => NULL, 'avg_volt' => NULL, 'ping_promedio' => NULL, 'registroValido' => false);
      $monitorizacionActivaOpciones = array('0','1');
      $tiposDispositivos = array('Host','Router','Switch','Firewall');
      $versionesSNMP = array('0','1','2','3');
      $expresionRegularFecha = '/(\d{4})-(\d{2})-(\d{2})/';
      $contadorNulos = 0;
      $i = 0;
      if (filter_var($this->ip, FILTER_VALIDATE_IP) && (filter_var($this->msk, FILTER_VALIDATE_INT) && $this->msk >= 0 && $this->msk <= 32))
      {
        $arregloErrores['direccionIP'] = 1;
      }
      if (preg_match($expresionRegularFecha, $this->fechaRegistroHost) == 1)
      {
        $arregloErrores['fechaRegistroHost'] = 2;
      }
      if (in_array($this->monitorizacionActiva, $monitorizacionActivaOpciones))
      {
        $arregloErrores['monitorizacionActiva'] = 3;
      }
      if (in_array($this->tipoDispositivo, $tiposDispositivos))
      {
        $arregloErrores['tipoDispositivo'] = 4;
      }
      if (in_array($this->versionSNMP, $versionesSNMP))
      {
        $arregloErrores['comunidadSNMP'] = 4;
      }
      $arregloErrores['versionSNMP'] = 4;
      if (($this->min_cpu > 0 && filter_var($this->min_cpu, FILTER_VALIDATE_INT)) || $this->min_cpu === 'NULL')
      {
        $arregloErrores['min_cpu'] = 5;
      }
    	if (($this->max_cpu > 0 && filter_var($this->max_cpu, FILTER_VALIDATE_INT)) || $this->max_cpu === 'NULL')
      {
        $arregloErrores['max_cpu'] = 6;
      }
    	if (($this->avg_cpu > 0 && filter_var($this->avg_cpu, FILTER_VALIDATE_INT)) || $this->avg_cpu === 'NULL')
      {
        $arregloErrores['avg_cpu'] = 7;
      }
    	if (($this->min_ram > 0 && filter_var($this->min_ram, FILTER_VALIDATE_INT)) || $this->min_ram === 'NULL')
      {
        $arregloErrores['min_ram'] = 8;
      }
    	if (($this->max_ram > 0 && filter_var($this->max_ram, FILTER_VALIDATE_INT)) || $this->max_ram === 'NULL')
      {
        $arregloErrores['max_ram'] = 9;
      }
    	if (($this->avg_ram > 0 && filter_var($this->avg_ram, FILTER_VALIDATE_INT)) || $this->avg_ram === 'NULL')
      {
        $arregloErrores['avg_ram'] = 10;
      }
    	if (($this->min_hdd > 0 && filter_var($this->min_hdd, FILTER_VALIDATE_INT)) || $this->min_hdd === 'NULL')
      {
        $arregloErrores['min_hdd'] = 11;
      }
    	if (($this->max_hdd > 0 && filter_var($this->max_hdd, FILTER_VALIDATE_INT)) || $this->max_hdd === 'NULL')
      {
        $arregloErrores['max_hdd'] = 12;
      }
    	if (($this->avg_hdd > 0 && filter_var($this->avg_hdd, FILTER_VALIDATE_INT)) || $this->avg_hdd === 'NULL')
      {
        $arregloErrores['avg_hdd'] = 13;
      }
    	if (($this->min_temp > 0 && filter_var($this->min_temp, FILTER_VALIDATE_INT)) || $this->min_temp === 'NULL')
      {
        $arregloErrores['min_temp'] = 14;
      }
    	if (($this->max_temp > 0 && filter_var($this->max_temp, FILTER_VALIDATE_INT)) || $this->max_temp === 'NULL')
      {
        $arregloErrores['max_temp'] = 15;
      }
    	if (($this->avg_temp > 0 && filter_var($this->avg_temp, FILTER_VALIDATE_INT)) || $this->avg_temp === 'NULL')
      {
        $arregloErrores['avg_temp'] = 16;
      }
    	if (($this->min_volt > 0 && filter_var($this->min_volt, FILTER_VALIDATE_INT)) || $this->min_volt === 'NULL')
      {
        $arregloErrores['min_volt'] = 17;
      }
    	if (($this->max_volt > 0 && filter_var($this->max_volt, FILTER_VALIDATE_INT)) || $this->max_volt === 'NULL')
      {
        $arregloErrores['max_volt'] = 18;
      }
    	if (($this->avg_volt > 0 && filter_var($this->avg_volt, FILTER_VALIDATE_INT)) || $this->avg_volt === 'NULL')
      {
        $arregloErrores['avg_volt'] = 19;
      }
      if (($this->ping_promedio > 0 && filter_var($this->ping_promedio, FILTER_VALIDATE_FLOAT)) || $this->ping_promedio === 'NULL')
      {
        $arregloErrores['ping_promedio'] = 20;
      }
      if ($oper === 'insertar' || $oper === 'actualizar')
      {
        foreach ($arregloErrores as &$columna)
        {
          if(is_null($columna))
          {
            $contadorNulos++;
          }
        }
      }
      else if ($oper === 'eliminar')
      {
        if (is_null($arregloErrores['direccionIP']))
        {
          $contadorNulos++;
        }
      }
      if ($contadorNulos === 0)
      {
        $arregloErrores['registroValido'] = true;
      }
      return $arregloErrores;
    }
  }
?>
