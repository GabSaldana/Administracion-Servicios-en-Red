#!/bin/sh
rrdtool create hdd.rrd -b now-100d -s 1d \
DS:hdd:GAUGE:1d:U:U \
RRA:AVERAGE:0.5:1:100 \
RRA:MAX:0.5:1:100 \
RRA:MIN:0.5:1:100 \
RRA:LAST:0.5:1:100 \
RRA:HWPREDICT:100:0.1:0.0035:20d 

rrdtool fetch hdd.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update hdd.rrd " $1 q; print cmd; s=.25*q;v=v+1; u=v/100; t=v%8; r=rand();q=3+s+v+2*u+3*(t-4)*(t-4)+10*r;if(v>70 && v< 80)q=0; system(cmd);}'

rrdtool graph hdd.png --start now-100d --end=now \
DEF:obs=hdd.rrd:hdd:AVERAGE \
DEF:max=hdd.rrd:hdd:MAX \
DEF:min=hdd.rrd:hdd:MIN \
DEF:pred=hdd.rrd:hdd:HWPREDICT \
DEF:dev=hdd.rrd:hdd:DEVPREDICT \
DEF:fail=hdd.rrd:hdd:FAILURES \
CDEF:scaledobs=obs,8,* \
CDEF:scaledpred=pred,8,* \
CDEF:scaledfail=fail,8,* \
TICK:scaledfail#ffffa0:1.0:"  Failures Average bits out" \
CDEF:scaledmax=max,8,* \
CDEF:scaledmin=min,8,* \
CDEF:upper=pred,dev,2,*,+ \
CDEF:lower=pred,dev,2,*,- \
CDEF:scaledupper=upper,8,* \
CDEF:scaledlower=lower,8,* \
LINE2:scaledobs#039be5:"Average bits out" \
LINE0.5:scaledupper#ff6d00:"Upper Bound Average bits out" \
LINE0.5:scaledlower#ff6d00:"Lower Bound Average bits out" \
LINE1:scaledpred#ff00FF:"Forecast " \
VDEF:slm=scaledobs,LSLSLOPE \
VDEF:slb=scaledobs,LSLINT \
CDEF:ls=scaledobs,COUNT,EXC,POP,slm,*,slb,+ \
CDEF:limite=ls,900,1000,LIMIT \
VDEF:minabc2=limite,FIRST \
LINE2:minabc2#aa0000 \
VDEF:lim100=limite,LAST \
LINE1:ls#333333:'Alcanze de 90 %\n' \
GPRINT:minabc2:'  Reach  90%  %c':strftime 


#rrdtool fetch test.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update test.rrd " $1 q; print cmd; s=.25*q;v=v+1; u=v/100; t=v%8; r=rand();q=3+s+v+2*u+3*(t-4)*(t-4)+10*r; system(cmd);}'

#rrdtool graph test.gif --start now-100d --end=now DEF:test=test.rrd:msgs:AVERAGE LINE2:test#FF0000:test 


#1d (dia), 1m(minuto), 1h (hora), 1M(mes) , 1y(año)

#Acepta valores cada 24 hrs (86400s)=1d con un heartbeat de 1d (si se monitorean datos por mas de 1 dia, estos se volveran dedconocidos)
#minimos y maximos  que pueden tomar los datos ,son cualquiera.
#unos registros son definidos tenemos uno que almacena 1.6 minutos (100s) de 1 segundo (1s)

#86400 s tiene un dia si hago muestras de 1d voy a obtener el valor que va en <period> de HWPREDICT (1d/1d)=1d

# creamos la DB retrocediendo una cantidad de dias dados para monitorear con un intervalo dado de tiempo (en este caso cada dia)
#Definimos los datos, dentro de que intervalo va a oscilar y que queremos guardar en este caso el promedio dado por:
#xff: tolerancia de la mitad de valores que se han recibido como verdaderos 
#steps:no de datos a ser tomados para procesar
#rows:tamaño de la BD dado por los dias retrocedidos.

#Basicamente estamos obteniendo 100 dias de monitoreo el cual estamos muestreando dia a dia(86400) tomando el promedio de estos con una 
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



