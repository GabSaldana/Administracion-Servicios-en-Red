import sys, os
import rrdtool
from functions import cambia
from functions import actualiza
#from functions import check_event

#python upRRD.py 127.0.0.2/2552552550 42 43 44 45 46 47

campos = ['cpu','ram','hdd','temp','volt','ping']        
valores = []        

if len(sys.argv) == 8:
    args = sys.argv
    ip = args[1]
    valores.append(args[2])#cpu
    valores.append(args[3])#ram
    valores.append(args[4])#hdd
    valores.append(args[5])#temp
    valores.append(args[6])#volt
    valores.append(args[7])#ping
    
    dirName=cambia(ip)
    #print dirName
    actualiza(dirName,valores)
    #check_event(valores,ip)
    comando = "python fails.py " + ip
    os.system(comando)

else:
    print "Ingrese los datos correctamente \n\n\tpython " + sys.argv[0] + " x.x.x.x/msk cpu ram hdd temp volt ping\n\n"    



