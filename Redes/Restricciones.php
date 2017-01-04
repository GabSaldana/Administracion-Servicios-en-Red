<?php
  require 'conexionMySQL.php';
  class Restricciones
  {
    private $ip;
    private $msk;
    private $direccionIP;
    private $campoMonitorizacion;
    private $valorAnticipacion;
    private $tipoValorAnticipacion;
    private $alertaActiva;
    private $conexion;

    function __construct($dirIP, $campoMonit, $valorAntic, $tipoValAntic, $alertaActiv, $oper)
    {
      $banderaRestricciones = false;
      $this->direccionIP = $dirIP;
      $cidr = explode("/", $this->direccionIP);
      $this->ip = $cidr[0];
      $this->msk = (int)$cidr[1];
      $this->campoMonitorizacion = $campoMonit;
      $this->valorAnticipacion = $valorAntic;
      $this->tipoValorAnticipacion = $tipoValAntic;
      $this->alertaActiva = $alertaActiv;
      $this->conexion = conexion();
      $arregloErrores = $this->validarCamposRestricciones($oper);
      if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
      {
        if ($oper === 'insertar')
        {
          if (!$this->comprobarExistenciaRestriccion() && $this->comprobarExistenciaDispositivo())
          {
            $banderaRestricciones = $this->insertarRestriccion();
          }
    		}
    		else if ($oper === 'actualizar')
        {
          if ($this->comprobarExistenciaRestriccion())
          {
            $banderaRestricciones = $this->actualizarRestriccion();
          }
    		}
    		else if ($oper === 'eliminar')
        {
          if ($this->comprobarExistenciaRestriccion())
          {
            $banderaRestricciones = $this->eliminarRestriccion();
          }
    		}
      }
      $this->conexion->close();
      //return $banderaRestricciones;
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

    private function comprobarExistenciaRestriccion()
    {
      $numeroFilasExistentes = 0;
      $existeRestriccion = false;
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
        $existeRestriccion = true;
      }
      return $existeRestriccion;
    }

    private function insertarRestriccion ()
    {
      $altaExitosa = false;
      $consulta = "CALL insertarRestriccion ('" . $this->direccionIP . "','" . $this->campoMonitorizacion . "'," . $this->valorAnticipacion . ",'" . $this->tipoValorAnticipacion . "','" . $this->alertaActiva . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $altaExitosa = true;
      }
      return $altaExitosa;
    }

    private function actualizarRestriccion ()
    {
      $actualizacionExitosa = false;
      $consulta = "CALL actualizarRestriccion ('" . $this->direccionIP . "','" . $this->campoMonitorizacion . "'," . $this->valorAnticipacion . ",'" . $this->tipoValorAnticipacion . "','" . $this->alertaActiva . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $actualizacionExitosa = true;
      }
      return $actualizacionExitosa;
    }

    private function eliminarRestriccion ()
    {
      $bajaExitosa = false;
      $consulta = "CALL eliminarRestriccion ('" . $this->direccionIP . "','" . $this->campoMonitorizacion . "'," . $this->valorAnticipacion . ",'" . $this->tipoValorAnticipacion . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $bajaExitosa = true;
      }
      return $bajaExitosa;
    }

    private function validarCamposRestricciones ($oper)
    {
      $arregloErrores = array('direccionIP' => NULL, 'campoMonitorizacion' => NULL, 'valorAnticipacion' => NULL, 'tipoValorAnticipacion' => NULL, 'alertaActiva' => NULL, 'registroValido' => false);
      $camposMonitorizacion = array('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt');
      $tiposValorMonitorizacion = array('seg','min','hor','dia','sem','mes');
      $opcionesAlerta = array('0','1');
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
      if ($oper === 'insertar' || $oper === 'actualizar')
      {
        if (in_array($this->alertaActiva, $opcionesAlerta))
        {
          $arregloErrores['alertaActiva'] = 5;
        }
      }
      else if ($oper === 'eliminar')
      {
        if ($this->alertaActiva === 'NULL')
        {
          $arregloErrores['alertaActiva'] = 5;
        }
      }
      foreach ($arregloErrores as &$columna)
      {
        if(is_null($columna))
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
