#!/bin/sh
rrdtool create prueba2.rrd -b now-100d -s 1d \
DS:msgs:GAUGE:1d:0:100 \
RRA:AVERAGE:0.5:1:100 \
RRA:HWPREDICT:100:0.1:0.0035:1d 

#rrdtool fetch prueba2.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update prueba2.rrd " $1 q; print cmd; s=.25*q;v=v+1; u=v/100; t=v%8; r=rand();q=150;if(v<50 ){q=2+s+v+1*u+2*(t-4)*(t-4)+r }; system(cmd);}'
rrdtool fetch prueba2.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update prueba2.rrd " $1 q; print cmd; v=v+1;if(v<100 ){q= 2 + v }; system(cmd);}'

rrdtool graph prueba2.png --start now-100d --end=now \
DEF:obs=prueba2.rrd:msgs:AVERAGE \
DEF:pred=prueba2.rrd:msgs:HWPREDICT \
DEF:dev=prueba2.rrd:msgs:DEVPREDICT \
DEF:fail=prueba2.rrd:msgs:FAILURES \
TICK:fail#ffffa0:1.0:"Failures Average bits out" \
CDEF:scaledobs=obs,8,* \
CDEF:upper=pred,dev,2,*,+ \
CDEF:lower=pred,dev,2,*,- \
CDEF:scaledupper=upper,8,* \
CDEF:scaledlower=lower,8,* \
HRULE:400#9c27b0:"Maximum allowed" \
HRULE:100#00c853:"Minimum allowed" \
LINE2:scaledobs#03a9f4:"Average bits out" \
LINE1:scaledupper#ff0000:"Upper Bound Average bits out" \
LINE1:scaledlower#ff0000:"Lower Bound Average bits out" 


#rrdtool fetch test.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update test.rrd " $1 q; print cmd; s=.25*q;v=v+1; u=v/100; t=v%8; r=rand();q=3+s+v+2*u+3*(t-4)*(t-4)+10*r; system(cmd);}'

#rrdtool graph test.gif --start now-100d --end=now DEF:test=test.rrd:msgs:AVERAGE LINE2:test#FF0000:test 


#1d (dia), 1m(minuto), 1h (hora), 1M(mes) , 1y(año)

#Acepta valores cada 24 hrs (86400s)=1d con un heartbeat de 1d (si se monitorean datos por mas de 1 dia, estos se volveran dedconocidos)
#minimos y maximos  que pueden tomar los datos ,son cualquiera.
#unos registros son definidos tenemos uno que almacena 1.6 minutos (100s) de 1 segundo (1s)

#86400 s tiene un dia si hago muestras de 1d voy a obtener el valor que va en <period> de HWPREDICT (1d/1d)=1d

# creamos la DB retrocediendo una cantidad de dias dados para monitorear con un intervalo dado de tiempo 
#(en este caso cada dia)
#Definimos los datos, dentro de que intervalo va a oscilar y que queremos guardar en este caso el promedio
# dado por:
#xff: tolerancia de la mitad de valores que se han recibido como verdaderos 
#steps:no de datos a ser tomados para procesar
#rows:tamaño de la BD dado por los dias retrocedidos.

#Basicamente estamos obteniendo 100 dias de monitoreo el cual estamos muestreando dia a dia(86400) tomando 
#el promedio de estos con una 
#tolerancia deerror de 1/2 por cada muestra

#RRA:HWPREDICT:<array length>:<alpha>:<beta>:<period>
#aarray length: The RRD file will store 5 days (1'440 data points) of forecasts and deviation predictions before wrap around
#number of predictions
#<alpha> is the intercept adaptation parameter, which must fall between 0 and 1. 0-1 indicators in the FAILURES RRA.
#<beta>:is the slope adaptation parameter, again between 0 and 1.
#Observations made during the last day (seasonal period),This value will be the RRA row counts for the SEASONAL and DEVSEASONAL RRAs.

#Revisando los valores de la BD rrdtool info test.rrd
#test.rrd tiempo: -valor ,sc3 lo llena

#DEF is a variable definition and it is a collection of data that changes trough time  DEF:var_name_1=some.rrd:ds_name:CF
#you fetch data from the rrd file from some registers that you can  especify in CF
#CDEF:var_name_2=posfix_expression the CDEF if for calculating values with this variables:
#Say you want to display bits per second (instead of bytes per second as stored in the database.) 
#You have to define a calculation (hence "CDEF") on variable "inbytes" and use that variable (inbits) instead of the original:
#CDEF:inbits=inbytes,8,* , inbits is the value retrieved form the stack
#The difference between Line1 and Line2 is the thicknes

#rrdfetch The fetch function is normally used internally by the graph function to get data from RRDs.
#fetch will analyze the RRD and try to retrieve the data in the resolution requested. The data fetched 
#is printed to stdout. *UNKNOWN* data is often represented by the string "NaN" depending on
#your OS's printf function. 
#rrdtool fetch filename CF [--resolution|-r resolution] [--start|-s start] 
#[--end|-e end] [--align-start|-a] [--daemon|-d address]

#the name of the RRD you want to fetch the data from.
#the consolidation function that is applied to the data you want to fetch (AVERAGE,MIN,MAX,LAST)
#start of the time series. A time in seconds since epoch (1970-01-01) is required. 
#the end of the time series in seconds since epoch. 



