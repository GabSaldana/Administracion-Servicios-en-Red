import sys, os
import time
import rrdtool
import tempfile
import smtplib
import MySQLdb
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders
from functions import send_image
from functions import getbaseline
from functions import cambia
from functions import crea_directorio
from functions import check_aberration
from functions import gen_image

#MAIN***********************************************************************    

#python fails.py 127.0.0.1/2552552550
enddate = int(time.mktime(time.localtime())) 
begdate = enddate - (86400)*100

# PARAMETROS PARA CONFIGURAR EL SMTP OUTLOOK
COMMASPACE = ', '
recipients = ['ela.ri.bag@outlook.com']
recipients.append('ela.ri.bag@gmail.com')
recipients.append('salmeanvicente@gmail.com')

# List of new aberrations
begin_ab = []
# List of gone aberrations
end_ab = []

width = '1000'
height = '800'

if len(sys.argv) == 2:
    
    args = sys.argv
    ip = args[1]
else:
    print "Ingrese los datos correctamente python " + sys.argv[0] + " x.x.x.x/msk"


path = os.getcwd()
dirName=cambia(ip)
pngpath = path + '/'+dirName+'/imagen'
ip_ = ip.split("/")[0]
ruta = path+'/'+dirName
print ruta # .... /Hosts/ip.../
os.chdir(ruta)

i=0
fecha_prediccion=''
maxi = []
mini = []
# List files and generate charts
for fname in os.listdir(ruta):
    
    if(fname):
        if(fname != "imagen"):
            print "fname:" + fname 
            print ip_
            maxi,mini=getbaseline(ip_)
            #print maxi
            #print i
            fecha_prediccion=gen_image(ruta, pngpath, fname.split(".")[0], width, height, begdate,enddate, maxi[i])
            i = i+1
            #fecha_prediccion = fecha_prediccion.split("% ")[1]
            #print "fecha prediccion *********" + fecha_prediccion

# Now check files for beiaberrations
for fname in os.listdir(ruta):
    if(fname != "imagen"):
        
        #Le pasamos el .rrd para revisar si hay alguna falla
        ab_status = check_aberration(ruta,fname)
        if ab_status == 1:
           begin_ab.append(fname)
        if ab_status == 2:
           end_ab.append(fname)
           #print "Error en:" + fname

    if len(begin_ab) > 0:
        send_image(pngpath,'New aberrations detected',recipients,begin_ab)
        print "send:"
        print 'New aberrations detected'
    if len(end_ab) > 0:
        print  end_ab
        send_image(pngpath,'Abberations',recipients,end_ab)
        print "send:"
        print 'Abberations gone'
