import sys
import rrdtool
import os
import tempfile
import time
import smtplib
import MySQLdb
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders

campos = ['cpu','ram','hdd','temp','volt','ping']

enddate = int(time.mktime(time.localtime())) 
begdate = enddate - (86400)*100
fecha_prediccion=''

width = '1000'
height = '800'

DB_HOST = 'localhost' 
DB_USER = 'root' 
DB_PASS = 'root' 
DB_NAME = 'AdministracionServiciosRed' 

valores = []

def create_rrd(campo):


    name = campo + ".rrd"
    ret = rrdtool.create(name, "--step", "300",
    "DS:"+campo+":GAUGE:600:0:100",
    "RRA:AVERAGE:0.5:12:168", #AVG de 60 minutos por 7 dias 12*60 = 1440
    "RRA:MAX:0.5:12:168",
    "RRA:MIN:0.5:12:168",
    "RRA:HWPREDICT:2016:0.5:0.1:3")
     
    if ret:
        print rrdtool.error()

def crea_directorio(nombre):

    path = os.getcwd()
    print path
    if not os.path.exists(nombre):
        os.mkdir(nombre)
    ruta = path+'/'+nombre
    print ruta
    os.chdir(ruta)
    for i in campos:
        create_rrd(i)

def cambia(ip):
    ip_ = ''    
    for i in ip:
        if i == "." or i == "/":
            ip_ += '_'
        else: 
            ip_ += i
    return ip_


def update(nameRRD, valor):
    st = 'N:'+ valor
    ret = rrdtool.update(nameRRD,st);
    if ret:
        print rrdtool.error()
    #print ret

def actualiza(dirName,valores):
    path = os.getcwd()
    ruta = path+'/'+dirName
    os.chdir(ruta)
    j=0
    for i in campos:
        name = i + ".rrd"
        #print name
        update(name,valores[j])
        #print name + " valor:" +valores[j]
        j=j+1
    os.chdir(path)

def check_aberration(rrdpath,fname):
    """ This will check for begin and end of aberration
        in file. Will return:
        0 if aberration not found. 
        1 if aberration begins
        2 if aberration ends
    """
    print "CHECK ABERRATION"
    ab_status = 0
    rrdfilename = rrdpath+'/'+fname
    campo = fname.split(".")[0]
    print "rrd:" + rrdfilename
    info = rrdtool.info(rrdfilename)
    rrdstep = int(info['step'])
    lastupdate = info['last_update']
    previosupdate = str((lastupdate - rrdstep*100) - 1)
    graphtmpfile = tempfile.NamedTemporaryFile()
    # Ready to get FAILURES  from rrdfile
    # will process failures array values for time of 2 last updates
    values = rrdtool.graph(graphtmpfile.name,
    'DEF:f0='+rrdfilename+':'+campo+':FAILURES:start='+previosupdate+':end='+str(lastupdate),
    'PRINT:f0:MIN:%1.0lf\n',
    'PRINT:f0:MAX:%1.0lf\n',
    'PRINT:f0:LAST:%1.0lf\n')                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
    fmin = int(values[2][0])
    fmax = int(values[2][1])
    flast = int(values[2][2])
    # check if failure value had changed.
    if (fmin != fmax):
        if (flast == 1):
            ab_status = 1
        else:
            ab_status = 2

    return ab_status 


    
def gen_image(rrdpath, pngpath, fname, width, height, begdate, enddate , campo):
    """
    Generates png file from rrd database:
    rrdpath - the path where rrd is located
    pngpath - the path png file should be created in
    fname - rrd file name, png file will have the same name .png extention
    width - chart area width
    height - chart area height
    begdate - unixtime
    enddate - unixtime  
    """
    #Pasando los valores minimos y maximos de la linea base
    
    umbral_esc = int(campo)*10
    print "valor umbral ",fname , umbral_esc
    umbral_= str(umbral_esc)

    # 24 hours before current time, will show on chart using SHIFT option  
    ldaybeg = str(begdate - (86400)*1)
    ldayend = str(enddate - (86400)*1)
    # Will show some additional info on chart 
    endd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(enddate)))).replace(':','\:')
    begd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(begdate)))).replace(':','\:')
    title = 'Chart for: '+fname
    # Files names 
    pngfname = pngpath+'/'+fname+'.png'
    print "luego:" +pngfname
    rrdfname = rrdpath+'/'+fname+'.rrd'
    # Get iformation from rrd file
    info = rrdtool.info(rrdfname)
    rrdtype = info['ds['+fname+'].type']
    # Will use multip variable for calculation of totals,should be usefull for internet traffic accounting,
    # or call/minutes count from CDR's. Do not need logic for DERIVE and ABSOLUTE
    if rrdtype == 'COUNTER':
        multip = str(int(enddate) - int(begdate))
    else:
        # if value type is GAUGE should divide time to step value
        rrdstep = info['step']
        multip = str(round((int(enddate) - int(begdate))/int(rrdstep)))
    
    # Make png image for base lines 
    values=rrdtool.graph(pngfname,
    '--width',width,'--height',height,
    '--start',str(begdate),'--end',str(enddate),'--title='+title+'LINEA BASE',
    '--lower-limit','0',
    '--slope-mode',
    'COMMENT:From\:'+begd_str+'  To\:'+endd_str+'\\c',
    'DEF:obs='+rrdfname+':'+fname+':AVERAGE',
    'DEF:max='+rrdfname+':'+fname+':MAX',
    'DEF:min='+rrdfname+':'+fname+':MIN',
    'DEF:pred='+rrdfname+':'+fname+':HWPREDICT',
    'DEF:dev='+rrdfname+':'+fname+':DEVPREDICT',
    'DEF:fail='+rrdfname+':'+fname+':FAILURES',
    'CDEF:scaledobs=obs,8,*',
    'CDEF:scaledpred=pred,8,*',
    'CDEF:scaledfail=fail,8,*',
    'TICK:scaledfail#ffffa0:1.0:"  Failures Average bits out\t"',
    'CDEF:scaledmax=max,8,*',
    'CDEF:scaledmin=min,8,*',
    'CDEF:upper=pred,dev,2,*,+',
    'CDEF:lower=pred,dev,2,*,-',
    'CDEF:scaledupper=upper,8,*',
    'CDEF:scaledlower=lower,8,*',
    'LINE2:scaledobs#039be5:"Average bits out\t"',
    'HRULE:'+umbral_+'#00c853:"Maximum cpu allowed\t"',
    'VDEF:slm=scaledobs,LSLSLOPE',
    'VDEF:slb=scaledobs,LSLINT',
    'CDEF:ls=scaledobs,COUNT,EXC,POP,slm,*,slb,+',
    'CDEF:limite=ls,0,1000,LIMIT',
    'VDEF:minabc2=limite,FIRST',
    'LINE2:minabc2#aa0000',
    'LINE0.8:scaledupper#4caf50:"Upper Bound Average bits out\t"',
    'LINE0.8:scaledlower#ff6d00:"Lower Bound Average bits out\t"',
    'LINE1:scaledpred#ff00FF:"Forecast\t"',
    'VDEF:lim100=limite,LAST',
    'LINE1:ls#333333:"Alcanze de 90 %\t"',
    'PRINT:minabc2:"Reach  90%  %c":strftime',
    'GPRINT:minabc2:"Reach  90%  %c":strftime')
    time_ = values[2][0]
    #print time_
    return time_

def send_image(pngpath,sub,recipients,flist):

    print "COLA DE IMAGENES:"
    print flist
    pngpath = pngpath + '/'
    me = "ela.ri.bag@gmail.com"
    my_password = "Manchas401:)"
    msg = MIMEMultipart()
    msg['Subject'] = sub
    msg['From'] = me
    msg['To'] = ", ".join(recipients)

    body = "Anexamos la imagen del monitoreo a su area de trabajo, la zona amarilla presenta una falla"
    msg.attach(MIMEText(body, 'plain'))

    for fname in flist:
        #Obtenemos el nombre de cada una de la imagenes de la carpeta de imagen
        print fname
        png_file = pngpath+fname.split('.rrd')[0]+'.png'
        print png_file

    html = '<html><body><p>Hi,XDXDXD!</p></body></html>'
    part2 = MIMEText(html, 'html')
    msg.attach(part2)

    attachment = open(png_file, "rb")
    part = MIMEBase('application', 'octet-stream')
    part.set_payload((attachment).read())
    encoders.encode_base64(part)
    part.add_header('Content-Disposition', "attachment; filename= %s" % png_file)
    msg.attach(part)

    # Send the message via gmail's regular server, over SSL - passwords are being sent, afterall
    s = smtplib.SMTP_SSL('smtp.gmail.com')
    s.login(me, my_password)
    s.sendmail(me, recipients, msg.as_string())
    print "enviando"
    s.quit()


def run_query(query=''): 
    datos = [DB_HOST, DB_USER, DB_PASS, DB_NAME] 
 
    conn = MySQLdb.connect(*datos) # Conectar a la base de datos 
    cursor = conn.cursor()         # Crear un cursor 
    cursor.execute(query)          # Ejecutar una consulta 
 
    if query.upper().startswith('SELECT'): 
        data = cursor.fetchall()   # Traer los resultados de un select 
        print 'Conexion establecida con la BD'
    else: 
        conn.commit()              # Hacer efectiva la escritura de datos 
        data = None  
    
    cursor.close()                 # Cerrar el cursor 
    conn.close()                   # Cerrar la conexion 
 
    return data

def getbaseline(ip=''):

    maxi = []
    mini = []
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
        versionSNMP = registro[4]
        comunidadSNMP = registro[5]
        ruta = registro[6]
        min_cpu = registro[7]
        max_cpu = registro[8]
        avg_cpu = registro[9]
        min_ram = registro[10]
        max_ram = registro[11]
        avg_ram = registro[12]
        min_hdd = registro[13]
        max_hdd = registro[14]
        avg_hdd = registro[15]
        min_temp = registro[16]
        max_temp = registro[17]
        avg_temp = registro[18]
        min_volt = registro[19]
        max_volt = registro[20]
        avg_volt = registro[21]
        ping_promedio = registro[22]

        maxi.append(max_cpu)
        maxi.append(max_ram)
        maxi.append(max_hdd)
        maxi.append(max_temp)
        maxi.append(max_volt)
        maxi.append(ping_promedio)

        mini.append(min_cpu)
        mini.append(min_ram)
        mini.append(min_hdd)
        mini.append(min_temp)
        mini.append(min_volt)
        mini.append(ping_promedio)
        # Imprimimos los resultados obtenidos
        #print "max_cpu=%s, min_cpu=%s, avg_cpu=%s " % (max_cpu,min_cpu,avg_cpu)
        #print "max_ram=%s, min_ram=%s, avg_ram=%s " % (max_ram,min_ram,avg_ram)
        #print "max_hdd=%s, min_hdd=%s, avg_hdd=%s " % (max_hdd,min_hdd,avg_hdd)
        #print "max_temp=%s, min_temp=%s, avg_temp=%s " % (max_temp,min_temp,avg_temp)
        #print "max_volt=%s, min_volt=%s, avg_volt=%s " % (max_volt,min_volt,avg_volt)
        #print "ping=%s" % (ping_promedio)
        
        #print maxi
        return maxi,mini


def setValorPred(direccionIP,campo,maxi):
    path = os.getcwd()
    dirName=cambia(direccionIP)
    pngpath = path + '/'+dirName+'/imagen'
    ruta = path+'/'+dirName
    fecha_prediccion=gen_image(ruta, pngpath,campo, width, height, begdate,enddate, maxi)
    #print "fecha prediccion *********" + fecha_prediccion
    return fecha_prediccion
    

def check_event(valores,direccionIP):

    i=0
    maxi,mini=getbaseline(direccionIP.split("/")[0])
    #print valores
    #print mini
    #print maxi
    for campo in campos:
        
        if (i != 5):

            if (int(mini[i])< int(valores[i]) and int(valores[i])<int(maxi[i])):
         
               #print "Valor capturado dentro"
               i = i+1
            else:
               print "Valor capturado fuera"
               fechaPrediccion=setValorPred(direccionIP,campo,maxi[i]).split("%")[1] 
               IP = direccionIP.split("/")[0]
               campoMonitorizacion = campo
               valorAnticipacion = maxi[i]
               tipoValorAnticipacion = "min"
               fechaSuceso = enddate
               #print "*****" + fechaPrediccion
               valorCapturado = valores[i]
               i =i+1

               insert_event(IP,campoMonitorizacion,valorAnticipacion,tipoValorAnticipacion,"CURRENT_TIMESTAMP",fechaPrediccion,valorCapturado)
        else:
            if(int(valores[i])<int(maxi[i])):
                print "Valor capturado dentro"
                i = i+1
            else:
                print "Valor capturado fuera"
                fechaPrediccion = setValorPred(direccionIP,campo,maxi[i]).split("%")[1]  
                IP = direccionIP.split("/")[0]
                campoMonitorizacion = campo
                valorAnticipacion = maxi[i]
                tipoValorAnticipacion = "min"
                fechaSuceso = enddate
                #print "*****" + fechaPrediccion
                valorCapturado = valores[i]
                i =i+1

                insert_event(IP,campoMonitorizacion,valorAnticipacion,tipoValorAnticipacion,"CURRENT_TIMESTAMP",fechaPrediccion,valorCapturado)



def insert_event(direccionIP, campoMonitorizacion, 
    valorAnticipacion, tipoValorAnticipacion, fechaSuceso, fechaPrediccion, valorCapturado):

    print "BITACORA*************************"
    print direccionIP
    print campoMonitorizacion
    print valorAnticipacion
    print tipoValorAnticipacion
    print fechaPrediccion
    print fechaSuceso
    print valorCapturado

    path = os.getcwd()#directorio donde va a estar almacenado este script junto con los de Hosts
    #os.chdir("..")
    comando = "php "+ '/home/gabs/Documents/Proyecto/Redes' + "/escuchaBitacoraSucesos.php" +" "+ \
    direccionIP +" "+campoMonitorizacion +" "+str(valorAnticipacion) +" "+ tipoValorAnticipacion +" " + \
    fechaSuceso +fechaPrediccion +" "+ str(valorCapturado)
    print comando
    #os.system(comando)
