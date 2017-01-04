import sys
import rrdtool
import os
from functions import cambia
from functions import crea_directorio

# ESTE SCRIPT RECIVE COMO PARAMETROS UNAIP PARA CREAR LA CARPETA Y POSTERIORMENTE LOS CAMPOS.rrd DENTRO 
#DE ELLA , ESTE SCRIPT DEBE ESTAR DENTRO DE HOSTS

#python crRRD.py 127.0.0.2/2552552550
if len(sys.argv) == 2:
    
    args = sys.argv
    ip = args[1]
else:
    print "Ingrese los datos correctamente python " + sys.argv[0] + " x.x.x.x/msk"

ip_=cambia(ip)
#print ip_
crea_directorio(ip_)
crea_directorio("imagen")
os.system("rm *.rrd")


