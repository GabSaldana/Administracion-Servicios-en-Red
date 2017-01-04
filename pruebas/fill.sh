#!/bin/sh
#The data used for updating the RRD was acquired at 'N', in which case the update time is set to be the current time. 
#he remaining elements of the argument are DS updates.

rrdtool fetch hdd.rrd AVERAGE -s -100d | awk '/:/ {cmd="rrdtool update hdd.rrd --template hdd  N:" q; print cmd; s=.25*q;v=v+1; u=v/100; t=v%8; r=rand();q=3+s+v+2*u+3*(t-4)*(t-4)+10*r; system(cmd);}'
#rrdtool graph ram.png --start now-7d --end=now DEF:ram=ram.rrd:ram:AVERAGE LINE2:ram#FF0000:ram 
