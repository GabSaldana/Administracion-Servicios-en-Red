#!/bin/sh

# cat initialize_database.sh
rrdtool create latency_db.rrd \
--step 60 \
DS:pl:GAUGE:120:0:100 \
DS:rtt:GAUGE:120:0:10000000 \
RRA:MAX:0.5:1:1500 \
