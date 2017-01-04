<?php
  require 'conexionMySQL.php';
  class permisosAdministracion
  {
    private $ip;
    private $msk;
    private $localEmailContacto;
    private $dominioEmailContacto;
    private $direccionIP;
    private $permisoActivo;
    private $conexion;

    function __construct($email, $dirIP, $permiso, $oper)
    {
      $banderaPermisosAdministracion = false;
      $em = explode("@", $email);
      $this->localEmailContacto = $em[0];
      $this->dominioEmailContacto = $em[1];
      $this->direccionIP = $dirIP;
      $cidr = explode("/", $this->direccionIP);
      $this->ip = $cidr[0];
      $this->msk = (int)$cidr[1];
      $this->permisoActivo = $permiso;
      $this->conexion = conexion();
      $arregloErrores = $this->validarCamposPermisosAdministracion($oper);
      if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
      {
        if ($oper === 'insertar')
        {
          if (!$this->comprobarExistenciaPermisosAdministracion && $this->comprobarExistenciaAdministrador() && $this->comprobarExistenciaDispositivo())
          {
            $banderaPermisosAdministracion = $this->insertarPermisosAdministracion();
          }
    		}
    		else if ($oper === 'actualizar')
        {
          if ($this->comprobarExistenciaPermisosAdministracion())
          {
            $banderaPermisosAdministracion = $this->actualizarPermisosAdministracion();
          }
    		}
    		else if ($oper === 'eliminar')
        {
          if ($this->comprobarExistenciaPermisosAdministracion())
          {
            $banderaPermisosAdministracion = $this->eliminarPermisosAdministracion();
          }
    		}
      }
      $this->conexion->close();
      //return $banderaPermisosAdministracion;
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

    private function comprobarExistenciaAdministrador()
    {
      $numeroFilasExistentes = 0;
      $existeAdministrador = false;
      $consulta = "SELECT * FROM administrador WHERE localEmailContacto = '" . $this->localEmailContacto . "' AND dominioEmailContacto = '" .$this->dominioEmailContacto . "';";
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
        $existeAdministrador = true;
      }
      return $existeAdministrador;
    }

    private function comprobarExistenciaPermisosAdministracion()
    {
      $numeroFilasExistentes = 0;
      $existePermisoAdministracion = false;
      $consulta = "SELECT * FROM permisosAdministracion WHERE localEmailContacto = '" . $this->localEmailContacto . "' AND dominioEmailContacto = '" .$this->dominioEmailContacto . "' AND direccionIP = '" . $this->direccionIP . "';";
      $resultadoQuery = $this->conexion->query($consulta);
      while ($fila = $resultadoQuery->fetch_assoc())
      {
        $numeroFilasExistentes++;
      }
      if ($numeroFilasExistentes > 0)
      {
        $existePermisoAdministracion = true;
      }
      return $existePermisoAdministracion;
    }

    private function insertarPermisosAdministracion ()
    {
      $altaExitosa = false;
      $consulta = "CALL insertarPermisosAdministracion ('" . $this->localEmailContacto . "','" . $this->dominioEmailContacto . "','" . $this->direccionIP . "','" . $this->permisoActivo . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $altaExitosa = true;
      }
      return $altaExitosa;
    }

    private function actualizarPermisosAdministracion ()
    {
      $actualizacionExitosa = false;
      $consulta = "CALL actualizarPermisosAdministracion ('" . $this->localEmailContacto . "','" . $this->dominioEmailContacto . "','" . $this->direccionIP . "','" . $this->permisoActivo . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $actualizacionExitosa = true;
      }
      return $actualizacionExitosa;
    }

    private function eliminarPermisosAdministracion ()
    {
      $bajaExitosa = false;
      $consulta = "CALL eliminarPermisosAdministracion ('" . $this->localEmailContacto . "','" . $this->dominioEmailContacto . "','" . $this->direccionIP . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $bajaExitosa = true;
      }
      return $bajaExitosa;
    }

    private function validarCamposPermisosAdministracion ($oper)
    {
      $email = $this->localEmailContacto."@".$this->dominioEmailContacto;
      $arregloErrores = array('localEmailContacto' => NULL, 'dominioEmailContacto' => NULL, 'direccionIP' => NULL, 'permisoActivo' => NULL, 'registroValido' => false);
      $opcionesPermisoActivo = array('0','1');
      $contadorNulos = 0;
      $i = 0;
      if (filter_var($email, FILTER_VALIDATE_EMAIL))
      {
        $arregloErrores['localEmailContacto'] = 1;
        $arregloErrores['dominioEmailContacto'] = 2;
      }
      if (filter_var($this->ip, FILTER_VALIDATE_IP) && (filter_var($this->msk, FILTER_VALIDATE_INT) && $this->msk >= 0 && $this->msk <= 32))
      {
        $arregloErrores['direccionIP'] = 3;
      }
      if ($oper === 'insertar' || $oper === 'actualizar')
      {
        if (in_array($this->permisoActivo, $opcionesPermisoActivo))
        {
          $arregloErrores['permisoActivo'] = 4;
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
      }
      else if ($oper === 'eliminar')
      {
        if (!is_null($arregloErrores['localEmailContacto']) && !is_null($arregloErrores['dominioEmailContacto']) && !is_null($arregloErrores['direccionIP']))
        {
          $arregloErrores['registroValido'] = true;
        }
      }
      return $arregloErrores;
    }
  }
?>
