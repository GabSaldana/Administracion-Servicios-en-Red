rrdtool graph grafica.png --alt-autoscale-max --rigid -E --start 1414333640 --end 1497405640 --width 1159 --height 400 -c \
BACK#FFFFFF -c SHADEA#EEEEEE00 -c SHADEB#EEEEEE00 -c FONT#000000 -c CANVAS#FFFFFF00 -c GRID#a5a5a5 -c MGRID#FF9999 -c \
FRAME#5e5e5e -c ARROW#5e5e5e -R normal --font LEGEND:8:'DejaVuSansMono' --font AXIS:7:'DejaVuSansMono' --font-render-mode normal \

COMMENT:'Bits/s Now Avg Max 95th \n' \
#---- BITS A SER MUESTREADOS
DEF:outoctets=port-2.rrd:OUTOCTETS:AVERAGE \
DEF:inoctets=port-2.rrd:INOCTETS:AVERAGE \
DEF:outoctets_max=port-2.rrd:OUTOCTETS:MAX \
DEF:inoctets_max=port-2.rrd:INOCTETS:MAX \
# SUMA DE LOS BITS DE ENTRADA MAS LOS DE SLAIDA
CDEF:octets=inoctets,outoctets,+ \
CDEF:doutoctets=outoctets,-1,*  \
#----PASANDOLO A BITS
CDEF:outbits=outoctets,8,* \
CDEF:outbits_max=outoctets_max,8,* \
#NOSE
CDEF:doutoctets_max=outoctets_max,-1,* \
#CONVIRTIENDO A BITS
CDEF:doutbits=doutoctets,8,* \
CDEF:doutbits_max=doutoctets_max,8,* \
CDEF:inbits=inoctets,8,* \
CDEF:inbits_max=inoctets_max,8,* \
#OBTIENDO EL TOTAL DE LOS BITS
VDEF:totin=inoctets,TOTAL \
VDEF:totout=outoctets,TOTAL \
VDEF:tot=octets,TOTAL \
#CALCULANDO EL 95% DE LOS BITS
VDEF:95thout=outbits,95,PERCENT \
VDEF:d95thout=outbits,95,PERCENT \
#DIBUJANDO EL AREA DE BITS DE ENTRADA
AREA:inbits_max#B6D14B: \
AREA:inbits#92B73F \
LINE1.25:inbits#4A8328:'In ' \
#IMPRIMIRNDO LOS VALORES NOW AVG Y MAX
GPRINT:inbits:LAST:%6.2lf%s \
GPRINT:inbits:AVERAGE:%6.2lf%s \
GPRINT:inbits_max:MAX:%6.2lf%s \
#DIBUJANDO EL AREA DE BITS DE SALIDA
AREA:doutbits_max#A0A0E5: \
AREA:doutbits#7075B8 \
LINE1.25:doutbits#323B7C:'Out' \
#IMPRIMIRNDO LOS VALORES NOW AVG Y MAX
GPRINT:outbits:LAST:%6.2lf%s \
GPRINT:outbits:AVERAGE:%6.2lf%s \
GPRINT:outbits_max:MAX:%6.2lf%s \
#IMPRIMIRNDO LOS VALORES DEL 95%
GPRINT:95thout:%6.2lf%s\\n \
#IMPRIMIRNDO LOS VALORES TOTALES
GPRINT:tot:'Total %6.2lf%s' \
GPRINT:totin:'(In %6.2lf%s' \
GPRINT:totout:'Out %6.2lf%s)\\l' \
# y = mx + b  = LSLSLOPE*x + LSLINT
VDEF:slm=outbits,LSLSLOPE \
VDEF:slb=outbits,LSLINT \
CDEF:ls=outbits,COUNT,EXC,POP,slm,*,slb,+ \
#que no pase de 90 y 100 %
CDEF:limite=ls,90,100,LIMIT \
# obtenemos la fecha minima esperada en la inicia la proyeccion de tiempo
VDEF:minabc2=limite,FIRST \
#dibujamos la linea base en 90
LINE2:minabc2#aa0000 \
# obtenemos la fecha maxima esperada que alcance el limite
VDEF:lim100=limite,LAST \
#dibujamos la linea tangente
LINE1:ls#333333:'Alcanze de 90 %\n' \
# imprimimos la fecha en la que se espera tener la falla y la hora con strftime
GPRINT:minabc2:'  Reach  90%  %c':strftime
