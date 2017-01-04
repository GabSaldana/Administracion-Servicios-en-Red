<?php
  require 'conexionMySQL.php';
  class Administrador
  {
    private $localEmailContacto;
    private $dominioEmailContacto;
    private $contrasenia;
    private $fechaRegistroAdministrador;
    private $logueoActivo;
    private $conexion;

    function __construct($email, $pass, $logueo, $oper)
    {
      $banderaAdministrador = false;
      $em = explode("@", $email);
      $this->localEmailContacto = $em[0];
      $this->dominioEmailContacto = $em[1];
      $this->contrasenia = md5($pass);
      $this->logueoActivo = $logueo;
      $this->fechaRegistroAdministrador = date("Y-m-d", time());
      $this->conexion = conexion();
      $arregloErrores = $this->validarCamposAdministrador($oper);
      if (!$this->conexion->connect_error && $arregloErrores['registroValido'])
      {
        if ($oper === 'insertar')
        {
          if (!$this->comprobarExistenciaAdministrador())
          {
            $banderaDispositivo = $this->insertarAdministrador();
          }
    		}
    		else if ($oper === 'actualizar')
        {
          if ($this->comprobarExistenciaAdministrador())
          {
            $banderaDispositivo = $this->actualizarAdministrador();
          }
    		}
    		else if ($oper === 'eliminar')
        {
          if ($this->comprobarExistenciaAdministrador())
          {
            $banderaDispositivo = $this->eliminarAdministrador();
          }
    		}
      }
      $this->conexion->close();
      //return $banderaAdministrador;
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

    private function insertarAdministrador ()
    {
      $altaExitosa = false;
      $consulta = "CALL insertarAdministrador ('" . $this->localEmailContacto . "','" . $this->dominioEmailContacto . "','" . $this->contrasenia . "','" . $this->fechaRegistroAdministrador . "','" . $this->logueoActivo ."')";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $altaExitosa = true;
      }
      return $altaExitosa;
    }

    private function actualizarAdministrador ()
    {
      $actualizacionExitosa = false;
      $consulta = "CALL actualizarAdministrador ('" . $this->localEmailContacto . "','" . $this->dominioEmailContacto . "','" . $this->contrasenia . "','" . $this->logueoActivo ."');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $actualizacionExitosa = true;
      }
      return $actualizacionExitosa;
    }

    private function eliminarAdministrador ()
    {
      $bajaExitosa = false;
      $consulta = "CALL eliminarAdministrador ('" . $this->localEmailContacto . "','". $this->dominioEmailContacto . "');";
      $resultadoQuery = $this->conexion->query($consulta);
      if ($resultadoQuery)
      {
        $bajaExitosa = true;
      }
      return $bajaExitosa;
    }

    private function validarCamposAdministrador ($oper)
    {
      $email = $this->localEmailContacto."@".$this->dominioEmailContacto;
      $arregloErrores = array('localEmailContacto' => NULL, 'dominioEmailContacto' => NULL, 'contrasenia' => NULL, 'fechaRegistroAdministrador' => NULL, 'logueoActivo' => NULL, 'registroValido' => false);
      $opcionesLogueoActivo = array('0','1');
      $expresionRegularTimeStamp = '/(\d{4})-(\d{2})-(\d{2})/';
      $expresionRegularMd5 = '/^[a-f0-9]{32}$/i';
      $contadorNulos = 0;
      $i = 0;
      if (filter_var($email, FILTER_VALIDATE_EMAIL))
      {
        $arregloErrores['localEmailContacto'] = 1;
        $arregloErrores['dominioEmailContacto'] = 2;
      }
      if (preg_match($expresionRegularMd5, $this->contrasenia))
      {
        $arregloErrores['contrasenia'] = 3;
      }
      if (preg_match($expresionRegularTimeStamp, $this->fechaRegistroAdministrador))
      {
        $arregloErrores['fechaRegistroAdministrador'] = 4;
      }
      if (in_array($this->logueoActivo, $opcionesLogueoActivo))
      {
        $arregloErrores['logueoActivo'] = 5;
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
        if ($contadorNulos === 0)
        {
          $arregloErrores['registroValido'] = true;
        }
      }
      if ($oper === 'eliminar')
      {
        if (!is_null($arregloErrores['localEmailContacto']) && !is_null($arregloErrores['dominioEmailContacto']))
        {
           $arregloErrores['registroValido'] = true;
        }
      }
      return $arregloErrores;
    }
  }
?>
