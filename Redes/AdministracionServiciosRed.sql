CREATE DATABASE AdministracionServiciosRed;
USE AdministracionServiciosRed;

CREATE TABLE IF NOT EXISTS `dispositivo` (
  `direccionIP` varchar(18) NOT NULL,
  `fechaRegistroHost` date NOT NULL,
  `monitorizacionActiva` enum('0','1') NOT NULL,
  `tipoDispositivo` enum('Host','Router','Switch','Firewall') NOT NULL,
  `versionSNMP` enum('0','1','2','3') NOT NULL,
  `comunidadSNMP` varchar(64) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `min_cpu` varchar(3),
  `max_cpu` varchar(3),
  `avg_cpu` varchar(3),
  `min_ram` varchar(3),
  `max_ram` varchar(3),
  `avg_ram` varchar(3),
  `min_hdd` varchar(3),
  `max_hdd` varchar(3),
  `avg_hdd` varchar(3),
  `min_temp` varchar(4),
  `max_temp` varchar(4),
  `avg_temp` varchar(4),
  `min_volt` varchar(3),
  `max_volt` varchar(3),
  `avg_volt` varchar(3),
  `ping_promedio` varchar(5),
  PRIMARY KEY (`direccionIP`),
  UNIQUE KEY `ruta` (`ruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `administrador` (
  `localEmailContacto` varchar(255) NOT NULL,
  `dominioEmailContacto` varchar(64) NOT NULL,
  `contrasenia` varchar(32) NOT NULL,
  `fechaRegistroAdministrador` date NOT NULL,
  `logueoActivo` enum('0','1') NOT NULL,
  PRIMARY KEY (`localEmailContacto`,`dominioEmailContacto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `permisosAdministracion` (
  `localEmailContacto` varchar(255) NOT NULL,
  `dominioEmailContacto` varchar(64) NOT NULL,
  `direccionIP` varchar(18) NOT NULL,
  `permisoActivo` enum('0','1') NOT NULL,
  PRIMARY KEY (`localEmailContacto`,`dominioEmailContacto`,`direccionIP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `permisosAdministracion` ADD CONSTRAINT emailPermisosAdministracion FOREIGN KEY (`localEmailContacto`,`dominioEmailContacto`) REFERENCES `administrador` (`localEmailContacto`,`dominioEmailContacto`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `permisosAdministracion` ADD CONSTRAINT ipPermisosAdministracion FOREIGN KEY (`direccionIP`) REFERENCES `dispositivo` (`direccionIP`) ON DELETE NO ACTION ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `restricciones` (
  `direccionIP` varchar(18) NOT NULL,
  `campoMonitorizacion` enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio') NOT NULL,
  `valorAnticipacion` smallint NOT NULL,
  `tipoValorAnticipacion` enum('min','hor','dia','sem','mes') NOT NULL,
  `alertaActiva` enum('0','1') NOT NULL,
  PRIMARY KEY (`direccionIP`,`campoMonitorizacion`,`valorAnticipacion`,`tipoValorAnticipacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `restricciones` ADD CONSTRAINT ipAlertas FOREIGN KEY (`direccionIP`) REFERENCES `dispositivo` (`direccionIP`) ON DELETE NO ACTION ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `bitacoraSucesos` (
  `direccionIP` varchar(18) NOT NULL,
  `campoMonitorizacion` enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio') NOT NULL,
  `valorAnticipacion` smallint NOT NULL,
  `tipoValorAnticipacion` enum('min','hor','dia','sem','mes') NOT NULL,
  `fechaSuceso` timestamp NOT NULL,
  `fechaPrediccion` timestamp NOT NULL,
  `valorCapturado` varchar(4) NOT NULL,
  PRIMARY KEY (`direccionIP`,`campoMonitorizacion`,`valorAnticipacion`,`tipoValorAnticipacion`,`fechaSuceso`,`fechaPrediccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `bitacoraSucesos` ADD CONSTRAINT alertaBitacoraSucesos FOREIGN KEY (`direccionIP`, `campoMonitorizacion`,`valorAnticipacion`,`tipoValorAnticipacion`) REFERENCES `restricciones` (`direccionIP`,`campoMonitorizacion`,`valorAnticipacion`,`tipoValorAnticipacion`) ON DELETE NO ACTION ON UPDATE CASCADE;

delimiter //
CREATE PROCEDURE insertarDispositivo (
  IN _direccionIP varchar(18),
  IN _fechaRegistroHost date,
  IN _monitorizacionActiva enum('0','1'),
  IN _tipoDispositivo enum('Host','Router','Switch','Firewall'),
  IN _versionSNMP enum('0','1','2','3'),
  IN _comunidadSNMP varchar(64),
  IN _ruta varchar(255),
  IN _min_cpu varchar(3),
  IN _max_cpu varchar(3),
  IN _avg_cpu varchar(3),
  IN _min_ram varchar(3),
  IN _max_ram varchar(3),
  IN _avg_ram varchar(3),
  IN _min_hdd varchar(3),
  IN _max_hdd varchar(3),
  IN _avg_hdd varchar(3),
  IN _min_temp varchar(4),
  IN _max_temp varchar(4),
  IN _avg_temp varchar(4),
  IN _min_volt varchar(3),
  IN _max_volt varchar(3),
  IN _avg_volt varchar(3),
  IN _ping_promedio varchar(5))
BEGIN
  INSERT INTO dispositivo(direccionIP, fechaRegistroHost, monitorizacionActiva, tipoDispositivo, versionSNMP, comunidadSNMP, ruta, min_cpu, max_cpu, avg_cpu, min_ram, max_ram, avg_ram, min_hdd, max_hdd, avg_hdd, min_temp, max_temp, avg_temp, min_volt, max_volt, avg_volt, ping_promedio) VALUES(_direccionIP, _fechaRegistroHost, _monitorizacionActiva, _tipoDispositivo, _versionSNMP, _comunidadSNMP, _ruta, _min_cpu, _max_cpu, _avg_cpu, _min_ram, _max_ram, _avg_ram, _min_hdd, _max_hdd, _avg_hdd, _min_temp, _max_temp, _avg_temp, _min_volt, _max_volt, _avg_volt, _ping_promedio);
END
//

delimiter //
CREATE PROCEDURE actualizarDispositivo (
  IN _direccionIP varchar(18),
  IN _monitorizacionActiva enum('0','1'),
  IN _tipoDispositivo enum('Host','Router','Switch','Firewall'),
  IN _versionSNMP enum('0','1','2','3'),
  IN _comunidadSNMP varchar(64),
  IN _min_cpu varchar(3),
  IN _max_cpu varchar(3),
  IN _avg_cpu varchar(3),
  IN _min_ram varchar(3),
  IN _max_ram varchar(3),
  IN _avg_ram varchar(3),
  IN _min_hdd varchar(3),
  IN _max_hdd varchar(3),
  IN _avg_hdd varchar(3),
  IN _min_temp varchar(4),
  IN _max_temp varchar(4),
  IN _avg_temp varchar(4),
  IN _min_volt varchar(3),
  IN _max_volt varchar(3),
  IN _avg_volt varchar(3),
  IN _ping_promedio varchar(5))
BEGIN
  UPDATE dispositivo
  SET monitorizacionActiva = _monitorizacionActiva, tipoDispositivo = _tipoDispositivo, versionSNMP = _versionSNMP, comunidadSNMP = _comunidadSNMP, min_cpu = _min_cpu, max_cpu = _max_cpu, avg_cpu = _avg_cpu, min_ram = _min_ram, max_ram = _max_ram, avg_ram = _avg_ram, min_hdd = _min_hdd, max_hdd = _max_hdd, avg_hdd = _avg_hdd, min_temp = _min_temp, max_temp = _max_temp, avg_temp = _avg_temp, min_volt = _min_volt, max_volt = _max_volt, avg_volt = _avg_volt, ping_promedio = _ping_promedio
  WHERE direccionIP = _direccionIP;
END
//

delimiter //
CREATE PROCEDURE eliminarDispositivo (IN _direccionIP varchar(18))
BEGIN
  DELETE FROM bitacoraSucesos WHERE direccionIP = _direccionIP;
  DELETE FROM restricciones WHERE direccionIP = _direccionIP;
  DELETE FROM permisosAdministracion WHERE direccionIP = _direccionIP;
  DELETE FROM dispositivo WHERE direccionIP = _direccionIP;
END
//

delimiter //
CREATE PROCEDURE insertarAdministrador (
IN _localEmailContacto varchar(255),
IN _dominioEmailContacto varchar(64),
IN _contrasenia varchar(32),
IN _fechaRegistroAdministrador date,
IN _logueoActivo enum('0','1'))
BEGIN
  INSERT INTO administrador(localEmailContacto, dominioEmailContacto, contrasenia, fechaRegistroAdministrador, logueoActivo) VALUES(_localEmailContacto, _dominioEmailContacto, _contrasenia, _fechaRegistroAdministrador, _logueoActivo);
END
//

delimiter //
CREATE PROCEDURE actualizarAdministrador (
IN _localEmailContacto varchar(255),
IN _dominioEmailContacto varchar(64),
IN _contrasenia varchar(32),
IN _logueoActivo enum('0','1'))
BEGIN
  UPDATE administrador
  SET contrasenia = _contrasenia, logueoActivo = _logueoActivo
  WHERE localEmailContacto = _localEmailContacto AND dominioEmailContacto = _dominioEmailContacto;
END
//

delimiter //
CREATE PROCEDURE eliminarAdministrador (
IN _localEmailContacto varchar(255),
IN _dominioEmailContacto varchar(64))
BEGIN
  DELETE FROM permisosAdministracion WHERE localEmailContacto = _localEmailContacto AND dominioEmailContacto = _dominioEmailContacto;
  DELETE FROM administrador WHERE localEmailContacto = _localEmailContacto AND dominioEmailContacto = _dominioEmailContacto;
END
//

delimiter //
CREATE PROCEDURE insertarPermisosAdministracion (
  IN _localEmailContacto varchar(255),
  IN _dominioEmailContacto varchar(64),
  IN _direccionIP varchar(18),
  IN _permisoActivo enum('0','1'))
BEGIN
  INSERT INTO permisosAdministracion(localEmailContacto, dominioEmailContacto, permisoActivo, direccionIP) VALUES(_localEmailContacto, _dominioEmailContacto, _permisoActivo, _direccionIP);
END
//

delimiter //
CREATE PROCEDURE actualizarPermisosAdministracion (
  IN _localEmailContacto varchar(255),
  IN _dominioEmailContacto varchar(64),
  IN _direccionIP varchar(18),
  IN _permisoActivo enum('0','1'))
BEGIN
  UPDATE permisosAdministracion
  SET permisoActivo = _permisoActivo
  WHERE localEmailContacto = _localEmailContacto AND dominioEmailContacto = _dominioEmailContacto AND direccionIP = _direccionIP;
END
//

delimiter //
CREATE PROCEDURE eliminarPermisosAdministracion (
  IN _localEmailContacto varchar(255),
  IN _dominioEmailContacto varchar(64),
  IN _direccionIP varchar(18))
BEGIN
  DELETE FROM permisosAdministracion WHERE localEmailContacto = _localEmailContacto AND dominioEmailContacto = _dominioEmailContacto AND direccionIP = _direccionIP;
END
//

delimiter //
CREATE PROCEDURE insertarRestriccion (
  IN _direccionIP varchar(18),
  IN _campoMonitorizacion enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio'),
  IN _valorAnticipacion smallint,
  IN _tipoValorAnticipacion enum('min','hor','dia','sem','mes'),
  IN _alertaActiva enum('0','1'))
BEGIN
  INSERT INTO restricciones(direccionIP, campoMonitorizacion, valorAnticipacion, tipoValorAnticipacion, alertaActiva) VALUES(_direccionIP, _campoMonitorizacion, _valorAnticipacion, _tipoValorAnticipacion, _alertaActiva);
END
//

delimiter //
CREATE PROCEDURE actualizarRestriccion (
  IN _direccionIP varchar(18),
  IN _campoMonitorizacion enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio'),
  IN _valorAnticipacion smallint,
  IN _tipoValorAnticipacion enum('min','hor','dia','sem','mes'),
  IN _alertaActiva enum('0','1'))
BEGIN
  UPDATE restricciones
  SET alertaActiva = _alertaActiva
  WHERE direccionIP = _direccionIP AND campoMonitorizacion = _campoMonitorizacion AND valorAnticipacion = _valorAnticipacion AND tipoValorAnticipacion = _tipoValorAnticipacion;
END
//

delimiter //
CREATE PROCEDURE eliminarRestriccion (
  IN _direccionIP varchar(18),
  IN _campoMonitorizacion enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio'),
  IN _valorAnticipacion smallint,
  IN _tipoValorAnticipacion enum('min','hor','dia','sem','mes'))
BEGIN
  DELETE FROM bitacoraSucesos WHERE direccionIP = _direccionIP AND campoMonitorizacion = _campoMonitorizacion AND valorAnticipacion = _valorAnticipacion AND tipoValorAnticipacion = _tipoValorAnticipacion;
  DELETE FROM restricciones WHERE direccionIP = _direccionIP AND campoMonitorizacion = _campoMonitorizacion AND valorAnticipacion = _valorAnticipacion AND tipoValorAnticipacion = _tipoValorAnticipacion;
END
//

delimiter //
CREATE PROCEDURE insertarBitacoraSucesos (
  IN _direccionIP varchar(18),
  IN _campoMonitorizacion enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio'),
  IN _valorAnticipacion smallint,
  IN _tipoValorAnticipacion enum('min','hor','dia','sem','mes'),
  IN _fechaSuceso timestamp,
  IN _fechaPrediccion timestamp,
  IN _valorCapturado varchar(4))
BEGIN
  INSERT INTO bitacoraSucesos(direccionIP, campoMonitorizacion, valorAnticipacion, tipoValorAnticipacion, fechaSuceso, fechaPrediccion, valorCapturado) VALUES(_direccionIP, _campoMonitorizacion, _valorAnticipacion, _tipoValorAnticipacion, _fechaSuceso, _fechaPrediccion, _valorCapturado);
END
//

delimiter //
CREATE PROCEDURE eliminarBitacoraSucesos (
  IN _direccionIP varchar(18),
  IN _campoMonitorizacion enum('min_cpu','max_cpu','min_ram','max_ram','min_hdd','max_hdd','min_temp','max_temp','min_volt','max_volt','ping_promedio'),
  IN _valorAnticipacion smallint,
  IN _tipoValorAnticipacion enum('min','hor','dia','sem','mes'),
  IN _fechaSuceso timestamp,
  IN _fechaPrediccion timestamp)
BEGIN
  DELETE FROM bitacoraSucesos WHERE direccionIP = _direccionIP AND campoMonitorizacion = _campoMonitorizacion AND valorAnticipacion = _valorAnticipacion AND tipoValorAnticipacion = _tipoValorAnticipacion AND fechaSuceso = _fechaSuceso AND fechaPrediccion = _fechaPrediccion;
END
//

/*
EJEMPLOS

php escuchaDispositivo.php '127.0.0.1/24' '1' 'Host' '1' 'public' 35 90 70 15 90 65 10 90 70 273 343 295 75 120 100 2.3 'insertar'
php escuchaDispositivo.php '127.0.0.2/24' '1' 'Host' '2' 'private' 35 90 70 15 90 65 10 90 70 273 343 295 75 120 100 1.5 'insertar'
php escuchaDispositivo.php '127.0.0.2/24' '1' 'Host' '1' 'private' 35 90 70 15 90 65 10 90 70 273 343 295 75 120 100 1.5 'actualizar'
php escuchaDispositivo.php '127.0.0.2/24'

php escuchaAdministrador.php salmeanvicente@gmail.com password '0' 'insertar'
php escuchaAdministrador.php salmeanvicente@hotmail.com password '0' 'insertar'
php escuchaAdministrador.php salmeanvicente@gmail.com password '1' 'actualizar'
php escuchaAdministrador.php salmeanvicente@hotmail.com

php escuchaPermisosAdministracion.php salmeanvicente@gmail.com '127.0.0.1/24' '1' 'insertar'
php escuchaPermisosAdministracion.php salmeanvicente@gmail.com '127.0.0.1/24' '0' 'actualizar'
php escuchaPermisosAdministracion.php salmeanvicente@gmail.com '127.0.0.1/24'

php escuchaRestricciones.php 127.0.0.1/24 'min_hdd' 5 'sem' '1' 'insertar'
php escuchaRestricciones.php 127.0.0.1/24 'max_volt' 5 'hor' '1' 'insertar'
php escuchaRestricciones.php 127.0.0.1/24 'max_volt' 5 'hor' '0' 'actualizar'
php escuchaRestricciones.php 127.0.0.1/24 'max_volt' 5 'hor'

php escuchaBitacoraSucesos.php 127.0.0.1/24 'min_hdd' 5 'sem' 8
php escuchaBitacoraSucesos.php 127.0.0.1/24 'min_hdd' 5 'sem' 6
php escuchaBitacoraSucesos.php 127.0.0.1/24 'min_hdd' 5 'sem' fechaSuceso fechaPrediccion

*/
