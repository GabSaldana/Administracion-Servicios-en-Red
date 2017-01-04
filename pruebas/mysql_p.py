import MySQLdb
 
DB_HOST = 'localhost' 
DB_USER = 'root' 
DB_PASS = 'root' 
DB_NAME = 'AdministracionServiciosRed' 
 
def run_query(query=''): 
    datos = [DB_HOST, DB_USER, DB_PASS, DB_NAME] 
 
    conn = MySQLdb.connect(*datos) # Conectar a la base de datos 
    cursor = conn.cursor()         # Crear un cursor 
    cursor.execute(query)          # Ejecutar una consulta 
 
    if query.upper().startswith('SELECT'): 
        data = cursor.fetchall()   # Traer los resultados de un select 
        print 'OK'
    else: 
        conn.commit()              # Hacer efectiva la escritura de datos 
        data = None  
    
    cursor.close()                 # Cerrar el cursor 
    conn.close()                   # Cerrar la conexion 
 
    return data

#SELECT QUERYS
print "*******************DISPOSITIVO***********************"

dato = raw_input("IP: ")
query = "SELECT * FROM dispositivo where direccionIP = '%s'" % dato
result =run_query(query)
print result

for registro in result:
      direccionIP = registro[0]
      fecharegistroHost = registro[1]
      monitorizacionActiva = registro[2]
      tipoDispositivo = registro[3]
      archivoRRDB = registro[4]
      imagenRRDB = registro[5]
      min_cpu = registro[6]
      max_cpu = registro[7]
      avg_cpu = registro[8]
      min_ram = registro[9]
      max_ram = registro[10]
      avg_ram = registro[11]
      min_hdd = registro[12]
      max_hdd = registro[13]
      avg_hdd = registro[14]
      min_temp = registro[15]
      max_temp = registro[16]
      avg_temp = registro[17]
      min_volt = registro[18]
      max_volt = registro[19]
      avg_volt = registro[20]
      # Imprimimos los resultados obtenidos
      print "max_cpu=%s, min_cpu=%s, avg_cpu=%s " % (max_cpu,min_cpu,avg_cpu)

print "********************ADMINISTRADOR**********************"
run_query('SELECT * FROM administrador;')
print "*********************BITACORASUCESOS*********************"
run_query('SELECT * FROM bitacoraSucesos;')
print "***********************PERMISOSADMINISTRACION*******************"
run_query('SELECT * FROM permisosAdministracion;')
print "**********************RESTRICCIONES********************"
run_query('SELECT * FROM restricciones;')

#INSERT QUERYS
print "******************************************"


query = "INSERT INTO dispositivo (direccionIP,fechaRegistroHost,monitorizacionActiva," \
+ "tipoDispositivo,archivoRRDB,imagenRRDB,min_cpu,max_cpu,avg_cpu,min_ram,max_ram,avg_ram," \
+ "min_hdd,max_hdd,avg_hdd,min_temp,max_temp,avg_temp,min_volt,max_volt,avg_volt)" \
+ " VALUES ('127.0.0.2', '2016-07-12', '0', 'Host'," \
+ "'/home/gabs/Documents/monitoring/python_example_mail/rrd/test2.rrd'," \
+ "'/home/gabs/Documents/monitoring/python_example_mail/imagen/test2.png'," \
+ "'20', '30', '50', '100', '213', '146','79', '82', '100', '215', '134', '144', '200', '50', '28');" 
#run_query(query)
print query

