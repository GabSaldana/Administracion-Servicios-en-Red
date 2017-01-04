#!/bin/sh
rrdtool create sc6.rrd -b now-100d -s 86400 \
DS:msgs:GAUGE:86400:U:U RRA:AVERAGE:0.5:1:100 \
RRA:HWPREDICT:100:0.1:0.0035:20 
#RRA:SEASONAL:20:0.1:0.0035:8 \
#RRA:DEVSEASONAL:20:0.1:0.0035:8 \
#RRA:DEVPREDICT:20:0.1:0.0035:8

rrdtool fetch sc7.rrd AVERAGE -s -100d | awk '/:/ {sub(",",".",$2);cmd="rrdtool update sc6.rrd " $1 $2; print cmd; system(cmd);}'

rrdtool graph sc6.png --start now-100d --end=now \
DEF:pred=sc6.rrd:msgs:HWPREDICT \
DEF:sc7=sc6.rrd:msgs:AVERAGE \
DEF:dev=sc6.rrd:msgs:DEVPREDICT \
CDEF:superior=pred,dev,2,*,+ \
CDEF:inferior=pred,dev,2,*,- \
DEF:fail=sc6.rrd:msgs:FAILURES  \
CDEF:falla=fail,200,* \
TICK:fail#ffffa0:1.0:”Failures” \
LINE2:sc7#FF0000:sc6 \
LINE2:pred#00FF00:prediccion \
LINE2:superior#0000FF:superior \
LINE2:inferior#00FFFF:inferior \
LINE2:falla#252500:fallas 


#Use this syntax:
#RRA:HWPREDICT:<length>:<alpha>:<beta>:<period>:<index of SEASONAL>
#RRA:SEASONAL:<period>:<gamma>:<index of HWPREDICT>

#The arguments of HWPREDICT are the same as before, with the addition of:
#<index of SEASONAL> 1-based index of the SEASONAL array in the order RRAs are specified in the create command.
#<period> is the number of primary data points in the seasonal period. It must match the value specified by the <period> argument #of HWPREDICT. It must be an integer greater than 2.
#The arguments of the SEASONAL RRA are:
#<gamma> is the adaptation parameter for seasonal coefficients, which must value between 0 and 1.
#<index of HWPREDICT> 1-based index of the HWPREDICT array in the order RRAs are specified in the create command

#Confidence bands can be created independently of aberrant behavior detection. In this case, create the four RRAs HWPREDICT, #SEASONAL, DEVSEASONAL, and DEVPREDICT but omit the FAILURES RRA.

#Use this syntax:
#RRA:HWPREDICT:<length>:<alpha>:<beta>:<period>:<index of SEASONAL>
#RRA:SEASONAL:<period>:<gamma>:<index of HWPREDICT>
#RRA:DEVSEASONAL:<period>:<gamma>:<index of HWPREDICT>
#RRA:DEVPREDICT:<array length>:<index of DEVSEASONAL>

#The arguments of HWPREDICT and SEASONAL are the same as before. The arguments of DEVSEASONAL and DEVPREDICT are:

#<period> is the number of primary data points in the seasonal period. It must match the value specified by the <period> argument of the HWPREDICT and SEASONAL arrays (this restriction may be lifted in a future implementation). It must be an integer greater than 2.
#<array length> is the number of deviations to store before wrap-around; this number must be longer than the seasonal period.
#<gamma> is the adaptation parameter for seasonal deviations, which must value between 0 and 1. It need not match the adaptation #parameter for the SEASONAL array.
#<index of HWPREDICT> 1-based index of the HWPREDICT array in the order RRAs are specified in the create command
#<index of DEVSEASONAL> 1-based index of the DEVSEASONAL array in the order RRAs are specified in the create command

#Finally, the FAILURES RRA can be create explicitly with the syntax, but at a minimum the HWPREDICT, SEASONAL, and DEVSEASONAL #arrays must be created as well. If confidence bands are also desired, create DEVPREDICT.

#Use this syntax:

#RRA:FAILURES:<length>:<threshold>:<window length>:<index of DEVSEASONAL>

#Where:
#<length> is the number of indicators (0,1 values) to store before wrap-around. A 1 indicates a failure: that is, the number of #violations in the last window of observations meets or exceeds the threshold.
#<threshold> is the minimum number of violations within a window (observed values outside the confidence bounds) that constitutes #a failure.
#<window length> is the number of time points in the window. Specify an integer greater than or equal to the threshold and less #than or equal to 28 (the maximum value).
#<index of DEVSEASONAL> 1-based index of the DEVSEASONAL array in the order RRAs are specified in the create command.

