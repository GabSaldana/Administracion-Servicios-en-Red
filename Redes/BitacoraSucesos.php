<?php
  require 'conexionMySQL.php';
  class BitacoraSucesos
  {
    private $ip;
    private $msk;
    private $direccionIP;
    private $campoMonitorizacion;
    private $valorAnticipacion;
    private $tipoValorAnticipacion;
    private $fechaSuceso;
    private $fechaPrediccion;
    private $valorCapturado;
    private $conexion;

    function __construct ($dirIP, $campoMon, $valorAntic, $tipoValorAntic, $fechaSuc, $fechaPred, $valorCap, $oper)
    {
      $banderaBitacoraSucesos = false;
      $this->direccionIP = $dirIP;
      $cidr = explode("/", $this->direccionIP);
      $this->ip = $cidr[0];
      $this->msk = (int)$cidr[1];
      $this->campoMonitorizacion = $campoMon;
      $this->valorAnticipacion = $valorAntic;
      $this->tipoValorAnticipacion = $tipoValorAntic;
      $this->conexion = conexion();
      if ($oper === 'insertar')
      {
        $this->valorCapturado = $valorCap;
        $ahora = time();
        $this->fechaSuceso = date("Y-m-d H:i:s", $ahora);
        switch ($this->tipoValorAnticipacion)
        {
          case 'seg':
            $aumento = $this->valorAnticipacion;
          break;
          case 'min':
            $aumento = $this->valorAnticipacion * 60;
          break;
          case 'hor':
            $aumento = $this->valorAnticipacion * 60 * 60;
          break;
          case 'dia':
            $aumento = $this->valorAnticipacion * 60 * 60 * 24;
          break;
          case 'sem':
            $aumento = $this->valorAnticipacion * 60 * 60 * 24 * 7;
          break;
          case 'mes':
            $aumento = $this->valorAnticipacion * 60 * 60 * 24 * 30;
          break;
        }
        $despues = $ahora + $aumento;
        $this->fechaPrediccion = date("Y-m-d H:i:s", $despues);
        $arregloErrores = $this->validarCamposBitacoraSucesos($oper);
        if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
        {
          if ($this->comprobarExistenciaRestriccion())
          {
            $banderaBitacoraSucesos = $this->insertarBitacoraSucesos();
          }
        }
      }
      else if ($oper === 'eliminar')
      {
        $this->fechaSuceso = $fechaSuc;
        $this->fechaPrediccion = $fechaPred;
        $arregloErrores = $this->validarCamposBitacoraSucesos($oper);
        if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
        {
          if ($this->comprobarExistenciaSuceso())
          {
            $banderaBitacoraSucesos = $this->eliminarBitacoraSucesos();
          }
        }
      }
      $this->conexion->close();
      //return $banderaBitacoraSucesos;
    }

    private function comprobarExistenciaRestriccion()
    {
      $numeroFilasExistentes = 0;
      $existeAlerta = false;
      $consulta = "SELECT * FROM restricciones WHERE direccionIP = '" . $this->direccionIP . "' AND campoMonitorizacion = '" . $this->campoMonitorizacion . "' AND valorAnticipacion = " . $this->valorAnticipacion . " AND tipoValorAnticipacion = '" . $this->tipoValorAnticipacion . "';";
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
        $existeAlerta = true;
      }
      return $existeAlerta;
    }

    private function comprobarExistenciaSuceso()
    {
      $numeroFilasExistentes = 0;
      $existeSuceso = false;
      $consulta = "SELECT * FROM bitacoraSucesos WHERE direccionIP = '" . $this->direccionIP . "' AND campoMonitorizacion = '" . $this->campoMonitorizacion . "' AND valorAnticipacion = " . $this->valorAnticipacion . " AND tipoValorAnticipacion = '" . $this->tipoValorAnticipacion . "' AND fechaSuceso = '" . $this->fechaSuceso . "' AND fechaPrediccion = '" . $this->fechaPrediccion . "';";
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
        $existeSuceso = true;
      }
      return $existeSuceso;
    }

    private function insertarBitacoraSucesos()
    {
      $altaExitosa = false;
      $consulta = "CALL insertarBitacoraSucesos ('" . $this->direccionIP . "','" . $this->campoMonitorizacion . "','" . $this->valorAnticipacion . "','" . $this->tipoValorAnticipacion . "','" . $this->fechaSuceso . "','" . $this->fechaPrediccion . "','". $this->valorCapturado . "')";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $altaExitosa = true;
      }
      return $altaExitosa;
    }

    private function eliminarBitacoraSucesos()
    {
      $bajaExitosa = false;
      $consulta = "CALL eliminarBitacoraSucesos ('" . $this->direccionIP . "','" . $this->campoMonitorizacion . "','" . $this->valorAnticipacion . "','" . $this->tipoValorAnticipacion . "','" . $this->fechaSuceso . "','" . $this->fechaPrediccion . "')";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $bajaExitosa = true;
      }
      return $bajaExitosa;
    }

    private function validarCamposBitacoraSucesos($oper)
    {
      $arregloErrores = array('direccionIP' => NULL, 'campoMonitorizacion' => NULL, 'valorAnticipacion' => NULL, 'tipoValorAnticipacion' => NULL, 'fechaSuceso' => NULL, 'fechaPrediccion' => NULL, 'valorCapturado' => NULL, 'registroValido' => false);
      $camposMonitorizacion = array('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt');
      $tiposValorMonitorizacion = array('seg','min','hor','dia','sem','mes');
      $expresionRegularTimeStamp = '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/';
      $contadorNulos = 0;
      $i = 0;
      if (filter_var($this->ip, FILTER_VALIDATE_IP) && (filter_var($this->msk, FILTER_VALIDATE_INT) && $this->msk >= 0 && $this->msk <= 32))
      {
        $arregloErrores['direccionIP'] = 1;
      }
      if (in_array($this->campoMonitorizacion, $camposMonitorizacion))
      {
        $arregloErrores['campoMonitorizacion'] = 2;
      }
      if (filter_var($this->valorAnticipacion, FILTER_VALIDATE_INT))
      {
        $arregloErrores['valorAnticipacion'] = 3;
      }
      if (in_array($this->tipoValorAnticipacion, $tiposValorMonitorizacion))
      {
        $arregloErrores['tipoValorAnticipacion'] = 4;
      }
      if (preg_match($expresionRegularTimeStamp, $this->fechaSuceso) == 1)
      {
        $arregloErrores['fechaSuceso'] = 5;
      }
      if (preg_match($expresionRegularTimeStamp, $this->fechaPrediccion) == 1)
      {
        $arregloErrores['fechaPrediccion'] = 6;
      }
      if ($oper === 'insertar')
      {
    		if (filter_var($this->valorCapturado, FILTER_VALIDATE_INT))
    		{
    			$arregloErrores['valorCapturado'] = 7;
    		}
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
        foreach ($arregloErrores as &$columna)
        {
          if (is_null($columna) && $i != 6 && $i != 7)
          {
            $contadorNulos++;
          }
          $i++;
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
