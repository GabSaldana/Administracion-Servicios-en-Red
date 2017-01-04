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
import conexion_mail

#PARAMETROS PARA CONECTARSE A LA BD
DB_HOST = 'localhost' 
DB_USER = 'root' 
DB_PASS = 'root' 
DB_NAME = 'AdministracionServiciosRed' 

# PARAMETROS PARA CONFIGURAR EL SMTP OUTLOOK
COMMASPACE = ', '
rrdpath = "/home/gabs/Documents/monitoring/python_example_mail/rrd/" 
width = '1000'
height = '800'

#PARAMETROS PARA ALMACENAR LOS VALORES DE LA LINEA BASE
min_cpu = ''
max_cpu = ''
avg_cpu = ''
min_ram = ''
max_ram = ''
avg_ram = ''
min_hdd = ''
max_hdd = ''
avg_hdd = ''
min_temp = ''
max_temp = ''
avg_temp = ''
min_volt = ''
max_volt = ''
avg_volt = ''

# DEFINICION DE FUNCIONES
enddate = int(time.mktime(time.localtime())) 
begdate = enddate - (86400)*100

    
def check_aberration(rrdpath,fname):
    """ This will check for begin and end of aberration
        in file. Will return:
        0 if aberration not found. 
        1 if aberration begins
        2 if aberration ends
    """
    print "CHECK ABERRATION"
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
     maxi_cpu,mini_cpu,maxi_ram , mini_ram,
    maxi_volt,mini_volt, maxi_temp,mini_temp,
    maxi_hdd,mini_hdd):
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
    max_cpu = int(maxi_cpu)*100
    min_cpu = int(mini_cpu)*100
    max_ram = int(maxi_ram)*100
    min_ram = int(mini_ram)*100
    max_volt = int(maxi_volt)*100
    min_volt = int(mini_volt)*100
    max_temp = int(maxi_temp)*100
    min_temp = int(mini_temp)*100
    max_hdd = int(maxi_hdd)*100
    min_hdd = int(mini_hdd)*100
    
    #print max_val 
    #print min_val
    maxi_cpu=str(max_cpu)
    mini_cpu=str(min_cpu)
    maxi_ram=str(max_ram)
    mini_ram=str(min_ram)
    maxi_volt=str(max_volt)
    mini_volt=str(min_volt)
    maxi_temp=str(max_temp)
    mini_temp=str(min_temp)
    maxi_hdd=str(max_hdd)
    mini_hdd=str(min_hdd)

    # 24 hours before current time, will show on chart using SHIFT option  
    ldaybeg = str(begdate - (86400)*1)
    ldayend = str(enddate - (86400)*1)
    # Will show some additional info on chart 
    endd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(enddate)))).replace(':','\:')
    begd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(begdate)))).replace(':','\:')
    title = 'Chart for: '+fname.split('.')[0]
    # Files names 
    pngfname = pngpath+fname.split('.')[0]+'.'+fname.split('.')[1]+'.'+fname.split('.')[2]+'.'+fname.split('.')[3]+'.png'
    print pngfname
    rrdfname = rrdpath+fname
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
    # Make png image
    rrdtool.graph(pngfname,
    '--width',width,'--height',height,
    '--start',str(begdate),'--end',str(enddate),'--title='+title,
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
    'LINE0.5:scaledupper#ff6d00:"Upper Bound Average bits out"',
    'LINE0.5:scaledlower#ff6d00:"Lower Bound Average bits out"',
    'HRULE:'+maxi_cpu+'#00c853:"Maximum cpu allowed"',
    'HRULE:'+mini_cpu+'#00c853:"Minimum cpu allowed"',
    'LINE1:scaledpred#ff00FF:"Forecast "',
    'VDEF:slm=scaledobs,LSLSLOPE',
    'VDEF:slb=scaledobs,LSLINT',
    'CDEF:ls=scaledobs,COUNT,EXC,POP,slm,*,slb,+',
    'CDEF:limite=ls,900,1000,LIMIT',
    'VDEF:minabc2=limite,FIRST',
    'LINE2:minabc2#aa0000',
    'VDEF:lim100=limite,LAST',
    'LINE1:ls#333333:"Alcanze de 90 %\n"',
    'GPRINT:minabc2:"Reach  90%  %c":strftime')


#MAIN***********************************************************************    
print "MAIN"
# List of new aberrations
begin_ab = []
# List of gone aberrations
end_ab = []

# List files and generate charts
for fname in os.listdir(rrdpath):
    print fname
    if(fname == ip):
        gen_image(rrdpath, pngpath, fname, width, height, begdate,
        enddate, max_cpu,min_cpu,max_ram,min_ram,
        max_volt,min_volt,max_temp,min_temp,max_hdd,min_hdd)
        
# Now check files for beiaberrations
for fname in os.listdir(rrdpath):
    print "Prediction"
    #send_alert_attached('Prediction',fname)
    ab_status = check_aberration(rrdpath,fname)
    if ab_status == 1:
       begin_ab.append(fname)
    if ab_status == 2:
       end_ab.append(fname)  

if len(begin_ab) > 0:
   #send_alert_attached('New aberrations detected',begin_ab)
   print "send:"
   print 'New aberrations detected'
if len(end_ab) > 0:
   #print end_ab
   #send_alert_attached('Abberations gone',end_ab)
   print "send:"
   print 'Abberations gone'

