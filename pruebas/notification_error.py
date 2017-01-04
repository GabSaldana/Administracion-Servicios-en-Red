import os
import time
import rrdtool
import tempfile
import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders


COMMASPACE = ', '
# Define params
rrdpath = "/home/gabs/Documents/Proyecto/pruebas/rrd/" 
pngpath = '/home/gabs/Documents/Proyecto/pruebas/imagen/'
width = '800'
height = '600'

#FOR OUTLOOK
smtpOutlook = "smtp-mail.outlook.com"
mailSend = "ela.ri.bag@outlook.com"
passSendO = "Manchas401:("
mailRec = "ela.ri.bag@gmail.com"


#FOR GMAIL

# Generate charts for last  hours
enddate = int(time.mktime(time.localtime())) 
begdate = enddate - (86400)*50

def gen_image(rrdpath, pngpath, fname, width, height, begdate, enddate):
    
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
    print "ABERRATION FOUND"
    # 24 hours before current time, will show on chart using SHIFT option  
    ldaybeg = str(begdate - (86400)*1)
    ldayend = str(enddate - (86400)*1)
    # Will show some additional info on chart 
    endd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(enddate)))).replace(':','\:')
    begd_str = time.strftime("%d/%m/%Y %H:%M:%S",(time.localtime(int(begdate)))).replace(':','\:')
    title = 'Chart for: '+fname.split('.')[0]
    # Files names 
    pngfname = pngpath+fname.split('.')[0]+'.png'
    rrdfname = rrdpath+fname
    # Get iformation from rrd file
    info = rrdtool.info(rrdfname)
    rrdtype = info['ds[cpu].type']
    # Will use multip variable for calculation of totals,
    # should be usefull for internet traffic accounting,
    # or call/minutes count from CDR's. 
    # Do not need logic for DERIVE and ABSOLUTE
    if rrdtype == 'COUNTER':
        multip = str(int(enddate) - int(begdate))
    else:
        # if value type is GAUGE should divide time to step value
        rrdstep = info['step']
        #conversion de tiempo a steps
        multip = str(round((int(enddate) - int(begdate))/int(rrdstep)))
        #print "TIME FOR THE PREDICTION: " + str(round(int(enddate) - int(begdate))) 
    # Make png image
    rrdtool.graph(pngfname,
    '--width',width,'--height',height,
    '--start',str(begdate),'--end',str(enddate),'--title='+title,
    '--lower-limit','0',
    '--slope-mode',
    'COMMENT:From\:'+begd_str+'  To\:'+endd_str+'\\c',
    'DEF:value='+rrdfname+':cpu:AVERAGE',
    'DEF:pred='+rrdfname+':cpu:HWPREDICT',
    'DEF:dev='+rrdfname+':cpu:DEVPREDICT',
    'DEF:fail='+rrdfname+':cpu:FAILURES',
    'DEF:yvalue='+rrdfname+':cpu:AVERAGE:start='+ldaybeg+':end='+ldayend,
    'SHIFT:yvalue:86400',
    'CDEF:upper=pred,dev,2,*,+',
    'CDEF:lower=pred,dev,2,*,-',
    'CDEF:ndev=dev,-1,*',
    'CDEF:tot=value,'+multip+',*',
    'CDEF:ytot=yvalue,'+multip+',*',
    'TICK:fail#FDD017:1.0:"Failures"\\n',
    'AREA:yvalue#C0C0C0:"Yesterday\:"',
    'GPRINT:ytot:AVERAGE:"TotalS\:%8.0lf"',
    'GPRINT:yvalue:MAX:"MaxS\:%8.0lf"',
    'GPRINT:yvalue:AVERAGE:"AverageS\:%8.0lf" \\n',
    'LINE3:value#0000ff:"Value    \:"',
    'GPRINT:tot:AVERAGE:"Total\:%8.0lf"',
    'GPRINT:value:MAX:"Max\:%8.0lf"',
    'GPRINT:value:AVERAGE:"Average\:%8.0lf" \\n',
    'LINE1:upper#ff0000:"Upper Bound "',
    'LINE1:pred#ff00FF:"Forecast "',
    'LINE1:ndev#000000:"Deviation "',
    'LINE1:lower#00FF00:"Lower Bound "')

def check_aberration(rrdpath,fname):
    #Se le pasa el nombre de rrd a usar
    
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
    'DEF:f0='+rrdfilename+':cpu:FAILURES:start='+previosupdate+':end='+str(lastupdate),
    'PRINT:f0:MIN:%1.0lf',
    'PRINT:f0:MAX:%1.0lf',
    'PRINT:f0:LAST:%1.0lf')  

    fmin = int(values[2][0])
    fmax = int(values[2][1])
    flast = int(values[2][2])
    # check if failure value had changed.
    # This will check for begin and end of aberration
    #    in file. Will return:
    #    0 if aberration not found. 
    #    1 if aberration begins
    #    2 if aberration ends
    
    if (fmin != fmax):
        if (flast == 1):
            ab_status = 1
        else:
            ab_status = 2
    return ab_status 


def send_alert_attached(subject, flist):
    msg = MIMEMultipart()
    msg['From'] = mailSend
    msg['To'] = mailRec
    msg['Subject'] = subject
    body = "Anexamos la imagen del monitoreo a su area de trabajo, la zona amarilla presenta una falla"
    msg.attach(MIMEText(body, 'plain'))
    i = 0

    for file in flist:
        i+= 1
        print i
        #Obtenemos el nombre de cada una de la imagenes de la carpeta de imagen
        png_file = pngpath+file.split('.')[0]+'.png'
        print png_file

    attachment = open(png_file, "rb")
    part = MIMEBase('application', 'octet-stream')
    part.set_payload((attachment).read())
    encoders.encode_base64(part)
    part.add_header('Content-Disposition', "attachment; filename= %s" % png_file)
    msg.attach(part)
    server = smtplib.SMTP(smtpOutlook, 25)
    server.starttls()
    server.login(mailSend, passSendO)
    text = msg.as_string()
    server.sendmail(mailSend, mailRec, text)
    server.quit()
    
    

# List of new aberrations
begin_ab = []
# List of gone aberrations
end_ab = []
# List files and generate charts
for fname in os.listdir(rrdpath):
    gen_image(rrdpath, pngpath, fname, width, height, begdate, enddate)
# Now check files for beiaberrations
for fname in os.listdir(rrdpath):
    ab_status = check_aberration(rrdpath,fname)
    if ab_status == 1:
       begin_ab.append(fname)
    if ab_status == 2:
       end_ab.append(fname)
if len(begin_ab) > 0:
   #send_alert_attached('New aberrations detected',begin_ab)
   print 'New aberrations detected'
if len(end_ab) > 0:
   print end_ab
   #send_alert_attached('Abberations gone',end_ab)
   print 'Abberations gone'
