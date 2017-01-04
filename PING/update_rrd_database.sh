# cat update_rrd_database.sh

#!/usr/local/bin/bash
#
### set the paths
command="/bin/ping -q -n -c 3"
gawk="/usr/bin/gawk "
rrdtool="/usr/bin/rrdtool "
#hosttoping="192.168.0.10"
hosttoping="127.0.0.1"
#hosttoping="255.255.255.0"
 


### data collection routine 
get_data() {
    echo "empezando..." 
    local output=$($command $1 2>&1)

    local method=$(echo "$output" | $gawk '
        BEGIN {pl=100; rtt=0.1}
        /packets transmitted/ {	
            match($0, /([0-9]+)% packet loss/, datapl)
            pl=datapl[1]
        }
        /min\/avg\/max/ {
            match($4, /(.*)\/(.*)\/(.*)\/(.*)/, datartt)
            rtt=datartt[2]
        }
        END {print pl ":" rtt}
        ')
    #echo  $method
    RETURN_DATA=$method
}
 
### change to the script directory
#cd /tools/rrdtool/latency/
 
### collect the data, se  le pasa como parametro a $hosttoping $1 es la ip enoutpt
get_data $hosttoping

### update the database
#The template switch allows you to specify which data sources you are going to update and in which order.
#The data used for updating the RRD was acquired at 'N', in which case the update time is set to be the current time. 
#he remaining elements of the argument are DS updates.
rrdtool update latency_db.rrd --template pl:rtt N:$RETURN_DATA

#rrdtool fetch latency_db.rrd MAX -s -25h | awk '/:/ {cmd="rrdtool update latency_db.rrd --template pl:rtt N:" $RETURN_DATA ; print cmd;  system(cmd);}'

echo "Returned data: " 
echo $RETURN_DATA 

#fetching data updated
#$rrdtool fetch latency_db.rrd MAX -s -1

