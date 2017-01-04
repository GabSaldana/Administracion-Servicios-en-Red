import os
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders



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

    html = '<html><body><p>Hi, I have the following alerts for you!</p></body></html>'
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

#recipients = ['ela.ri.bag@outlook.com']
#recipients.append('ela.ri.bag@gmail.com')
#imagenes = []
#for fname in os.listdir(pngpath):
#    imagenes.append(fname)
    #print fname
#send_image("alrt",recipients,imagenes)