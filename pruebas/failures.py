import os
import time
import rrdtool
import tempfile
import smtplib
import MySQLdb
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders
from conexion_mail import send_image
from getbaseline import getbaseline


# PARAMETROS PARA CONFIGURAR EL SMTP OUTLOOK
COMMASPACE = ', '
rrdpath = "/home/gabs/Documents/monitoring/python_example_mail/rrd/" 
pngpath = '/home/gabs/Documents/monitoring/python_example_mail/imagen/'
width = '1000'
height = '800'


# DEFINICION DE FUNCIONES
enddate = int(time.mktime(time.localtime())) 
begdate = enddate - (86400)*100

def point(ip):
    
    print "point: " + ip
    ip_=""
    for i in ip:
        if i == "_":
            ip_ += "."
        else:
            ip_ += i
    return ip_


def check_aberration(rrdpath,fname):
    """ This will check for begin and end of aberration
        in file. Will return:
        0 if aberration not found. 
        1 if aberration begins
        2 if aberration ends
    """
    #print "CHECK ABERRATION"
    ab_status = 0
    rrdfilename = rrdpath+fname
    info = rrdtool.info(rrdfilename)
    rrdstep = int(info['step'])
    lastupdate = info['last_update']
    previosupdate = str((lastupdate - rrdstep*100) - 1)
    graphtmpfile = tempfile.NamedTemporaryFile()
    # Ready to get FAILURES  from rrdfile
    # will process failures array values for time of 2 last updates
    values = rrdtool.graph(graphtmpfile.name,
    'DEF:f0='+rrdfilename+':msgs:FAILURES:start='+previosupdate+':end='+str(lastupdate),
    'PRINT:f0:MIN:%1.0lf',
    'PRINT:f0:MAX:%1.0lf',
    'PRINT:f0:LAST:%1.0lf')                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
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
    
def gen_image(rrdpath, pngpath, fname, width, height, begdate, enddate , 
     maxi_cpu,maxi_ram ,maxi_temp,maxi_hdd,maxi_volt,ping_promedio):
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
    print "GRAP"
    max_cpu = int(maxi_cpu)*10
    max_ram = int(maxi_ram)*10
    max_volt = int(maxi_volt)*10
    max_temp = int(maxi_temp)*10
    max_hdd = int(maxi_hdd)*10
    ping = int(ping_promedio)*10
    
    #print max_val 
    #print min_val
    maxi_cpu=str(max_cpu)
    maxi_ram=str(max_ram)
    maxi_volt=str(max_volt)
    maxi_temp=str(max_temp)
    maxi_hdd=str(max_hdd)
    ping_promedio = str(ping)

    # 24 hours before current time, will show on chart using SHIFT option  
    ldaybeg = str(begdate - (86400)*1)
    ldayend = str(enddate - (86400)*1)
    # Will show some additional info on chart 
    endd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(enddate)))).replace(':','\:')
    begd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(begdate)))).replace(':','\:')
    title = 'Chart for: '+fname
    # Files names 
    pngfname = pngpath+fname+'.png'
    print "luego:" + pngfname
    rrdfname = rrdpath+fname+'.rrd'
    # Get iformation from rrd file
    info = rrdtool.info(rrdfname)
    rrdtype = info['ds[msgs].type']
    # Will use multip variable for calculation of totals,
    # should be usefull for internet traffic accounting,
    # or call/minutes count from CDR's. 
    # Do not need logic for DERIVE and ABSOLUTE
    if rrdtype == 'COUNTER':
        multip = str(int(enddate) - int(begdate))
    else:
        # if value type is GAUGE should divide time to step value
        rrdstep = info['step']
        multip = str(round((int(enddate) - int(begdate))/int(rrdstep)))
    
    # Make png image for base lines 
    rrdtool.graph(pngfname,
    '--width',width,'--height',height,
    '--start',str(begdate),'--end',str(enddate),'--title='+title+'LINEA BASE',
    '--lower-limit','0',
    '--slope-mode',
    'COMMENT:From\:'+begd_str+'  To\:'+endd_str+'\\c',
    'DEF:obs='+rrdfname+':msgs:AVERAGE',
    'DEF:max='+rrdfname+':msgs:MAX',
    'DEF:min='+rrdfname+':msgs:MIN',
    'DEF:pred='+rrdfname+':msgs:HWPREDICT',
    'DEF:dev='+rrdfname+':msgs:DEVPREDICT',
    'DEF:fail='+rrdfname+':msgs:FAILURES',
    'CDEF:scaledobs=obs,8,*',
    'CDEF:scaledpred=pred,8,*',
    'CDEF:scaledfail=fail,8,*',
    'TICK:scaledfail#ffffa0:1.0:"  Failures Average bits out"',
    'CDEF:scaledmax=max,8,*',
    'CDEF:scaledmin=min,8,*',
    'CDEF:upper=pred,dev,2,*,+',
    'CDEF:lower=pred,dev,2,*,-',
    'CDEF:scaledupper=upper,8,*',
    'CDEF:scaledlower=lower,8,*',
    'LINE2:scaledobs#039be5:"Average bits out"',
    'HRULE:'+maxi_cpu+'#00c853:"Maximum cpu allowed\n"',
    'HRULE:'+maxi_ram+'#8e24aa:"Maximum ram allowed\n"',
    'HRULE:'+maxi_hdd+'#ff4081:"Maximum hdd allowed\n"',
    'HRULE:'+maxi_temp+'#ffeb3b:"Maximum temp allowed\n"',
    'HRULE:'+maxi_volt+'#ff9800:"Maximum volt allowed\n"',
    'HRULE:'+ping_promedio+'#00c853:"Maximum ping allowed"',
    'VDEF:slm=scaledobs,LSLSLOPE',
    'VDEF:slb=scaledobs,LSLINT',
    'CDEF:ls=scaledobs,COUNT,EXC,POP,slm,*,slb,+',
    'CDEF:limite=ls,900,1000,LIMIT',
    'VDEF:minabc2=limite,FIRST',
    'LINE2:minabc2#aa0000',
    'VDEF:lim100=limite,LAST',
    'LINE1:ls#333333:"Alcanze de 90 %\n"',
    'GPRINT:minabc2:"Reach  90%  %c":strftime')

    # Make png image for desviations 
    pngfname = pngfname.split('.png')[0]+"forecast"+'.png'
    rrdtool.graph(pngfname,
    '--width',width,'--height',height,
    '--start',str(begdate),'--end',str(enddate),'--title='+title+'FORECAST',
    '--lower-limit','0',
    '--slope-mode',
    'COMMENT:From\:'+begd_str+'  To\:'+endd_str+'\\c',
    'DEF:obs='+rrdfname+':msgs:AVERAGE',
    'DEF:max='+rrdfname+':msgs:MAX',
    'DEF:min='+rrdfname+':msgs:MIN',
    'DEF:pred='+rrdfname+':msgs:HWPREDICT',
    'DEF:dev='+rrdfname+':msgs:DEVPREDICT',
    'DEF:fail='+rrdfname+':msgs:FAILURES',
    'CDEF:scaledobs=obs,8,*',
    'CDEF:scaledpred=pred,8,*',
    'CDEF:scaledfail=fail,8,*',
    'TICK:scaledfail#ffffa0:1.0:"  Failures Average bits out"',
    'CDEF:scaledmax=max,8,*',
    'CDEF:scaledmin=min,8,*',
    'CDEF:upper=pred,dev,2,*,+',
    'CDEF:lower=pred,dev,2,*,-',
    'CDEF:scaledupper=upper,8,*',
    'CDEF:scaledlower=lower,8,*',
    'LINE2:scaledobs#039be5:"Average bits out"',
    'LINE0.8:scaledupper#4caf50:"Upper Bound Average bits out"',
    'LINE0.8:scaledlower#ff6d00:"Lower Bound Average bits out"',
    'LINE1:scaledpred#ff00FF:"Forecast "')

#MAIN***********************************************************************    

#administradores
recipients = ['ela.ri.bag@outlook.com']
recipients.append('ela.ri.bag@gmail.com')

resp = []
ping_promedio=''
# List of new aberrations
begin_ab = []
# List of gone aberrations
end_ab = []
#List for prediction
pred = []

# List files and generate charts
for fname in os.listdir(rrdpath):
    
    if(fname):
        print "fname:" + fname 
        ip1=fname.split('.png')[0] 
        ip2 = ip1.split(".rrd")[0]
        ip=point(ip1).split(".rrd")[0]
        print "ip: " + ip      #print ip
        resp=getbaseline(ip)
        #print resp
        gen_image(rrdpath, pngpath, ip2, width, height, begdate,
        enddate, resp[0],resp[1],resp[2],resp[3],resp[4],resp[5])
        
# Now check files for beiaberrations
for fname in os.listdir(rrdpath):
    #print "Prediction"
    print "Para" + fname
    #Le pasamos el .rrd para revisar si hay alguna falla
    ab_status = check_aberration(rrdpath,fname)
    if ab_status == 1:
       begin_ab.append(fname)
    if ab_status == 2:
       end_ab.append(fname)  

for fname in os.listdir(pngpath):
    if(fname.find("forecast")> 0):
        print "forecast:" +  fname
        pred.append(fname.split('.png')[0])
send_image('Forcasting for this device',recipients,pred)

if len(begin_ab) > 0:
   send_image('New aberrations detected',recipients,begin_ab)
   print "send:"
   print 'New aberrations detected'
if len(end_ab) > 0:
   #print end_ab
   send_image('Abberations',recipients,end_ab)
   print "send:"
   print 'Abberations gone'

