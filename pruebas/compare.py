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

def getbaseline(ip=''):

    #SELECT QUERYS
    #CONSULTA LA LINEA BASE
    query = "SELECT * FROM dispositivo where direccionIP = '%s'" % ip
    result =run_query(query)
    #print result

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
        ping_promedio = registro[21]

      
        # Imprimimos los resultados obtenidos
        print "max_cpu=%s, min_cpu=%s, avg_cpu=%s " % (max_cpu,min_cpu,avg_cpu)
        print "max_ram=%s, min_ram=%s, avg_ram=%s " % (max_ram,min_ram,avg_ram)
        print "max_hdd=%s, min_hdd=%s, avg_hdd=%s " % (max_hdd,min_hdd,avg_hdd)
        print "max_temp=%s, min_temp=%s, avg_temp=%s " % (max_temp,min_temp,avg_temp)
        print "max_volt=%s, min_volt=%s, avg_volt=%s " % (max_volt,min_volt,avg_volt)




# __.__.___.__  ___ ___ ___ ___ ___


#print "VALORES ACTUALES: IP:%s cpu=%s ram=%s hdd=%s temp=%s volt=%s" %ip  %cpu %ram %hdd %temp %volt 

