#! /usr/bin/perl 

use lib qw( /opt/rrdtool-1.4.5/lib/perl );

use RRDp;

# this simpulates a standard mrtg-2.x setup ... we can use this to
# compare performance ...

$main::DEBUG=0;
$STEP = 300;
$RUNS = 12*24*30*6;
$GRUNS = 20;
$RRD = "piped-demo.rrd";
$SVG = "piped-demo.svg";
$PNG = "piped-demo.png";

# some magic to find the correct rrdtol executable
$prefix="/opt/rrdtool-1.4.5";

if ( -x "${prefix}/bin/rrdtool") {
   RRDp::start "${prefix}/bin/rrdtool";
} elsif ( -x "../../../bin/rrdtool") {
   RRDp::start "../../../bin/rrdtool";
} else {
   RRDp::start "../src/rrdtool";
}

print "* Creating RRD with properties equivalent to mrtg-2.x logfile\n\n";

$START = time()-$RUNS*$STEP;

RRDp::cmd "create $RRD -b $START -s $STEP 
	DS:in:GAUGE:400:U:U
	DS:out:GAUGE:400:U:U
	RRA:AVERAGE:0.5:1:600
 	RRA:AVERAGE:0.5:6:600
 	RRA:MAX:0.5:6:600
 	RRA:AVERAGE:0.5:24:600
 	RRA:MAX:0.5:24:600
 	RRA:AVERAGE:0.5:144:600
 	RRA:MAX:0.5:144:600";

$answer = RRDp::read;
($user,$sys,$real) =  ($RRDp::user,$RRDp::sys,$RRDp::real);
    
print "* Filling RRD with $RUNS Values. One moment please ...\n";
print "  If you are running over NFS this will take *MUCH* longer\n\n"; 

for ($i=$START+1;
     $i<$START+$STEP*$RUNS;
     $i+=$STEP+int((rand()-0.5)*7)){

  $line = "update $RRD $i:".int(rand(100000)).":".int(rand(100000));
  RRDp::cmd $line;
  $answer = RRDp::read;
}

($user1,$sys1,$real1) =  ($RRDp::user,$RRDp::sys,$RRDp::real);

printf "-- performance analysis Update test\n".
       "   usr/upd: %1.5fs sys/upd: %1.5fs real/upd: %1.5fs upd/sec: %1.0f\n",
  ($user1-$user)/($RUNS), ($sys1-$sys)/($RUNS), 
  ($real1-$real)/($RUNS), ($RUNS)/($real1-$real);
print "\n";
# creating some graphs

print "* Creating $GRUNS SVG graphs: $SVG\n\n";
$now = time;
$localtime = scalar localtime(time);
$localtime = s/:/\\:/g;
for ($i=0;$i<$GRUNS;$i++) {
RRDp::cmd "graph $SVG ", "--title 'Test GRAPH' ",
	"--imgformat SVG --height 150 --vertical-label 'Dummy Units' ".
	"--start now".(-$RUNS*$STEP),
	"--color ARROW#bfbfbf",
        "DEF:alpha=$RRD:in:AVERAGE",
        "DEF:beta=$RRD:out:AVERAGE",
        "CDEF:calc=alpha,beta,+,1.5,/",
        "AREA:alpha#0022e9:Alpha",
        "STACK:beta#00b871:Beta",
        "STACK:calc#ff0091:Calc\\j",
	"PRINT:alpha:AVERAGE:'Average Alpha\\: %1.2lf %S'",
	"PRINT:alpha:MIN:'Min Alpha\\: %1.2lf %S'",
	"PRINT:alpha:MAX:'Max Alpha\\: %1.2lf %S'",
	"GPRINT:calc:AVERAGE:'Average calc\\: %1.2lf %S\\r'",
	"GPRINT:calc:MIN:'Min calc\\: %1.2lf %S'",
	"GPRINT:calc:MAX:'Max calc\\: %1.2lf %S'",
        "VRULE:".($now-3600)."#008877:'60 Minutes ago'",
        "COMMENT:'\\s'",
        "COMMENT:'Graph created on\\: ".$localtime."\\c'";

$answer = RRDp::read;
}
($user2,$sys2,$real2) =  ($RRDp::user,$RRDp::sys,$RRDp::real);

print "ANSWER:\n$$answer";

printf "\n-- average Time for one Graph\n".
       "   usr/grf: %1.5fs sys/grf: %1.5fs real/grf: %1.5fs   graphs/sec: %1.2f\n",
  ($user2-$user1)/$GRUNS, 
  ($sys2-$sys1)/$GRUNS, 
  ($real2-$real1)/$GRUNS, 
  $GRUNS/($real2-$real1);

print "\n\n* Creating $GRUNS PNG graphs: $PNG\n\n";

$now = time;
($user1,$sys1,$real1) =  ($RRDp::user,$RRDp::sys,$RRDp::real);
my $local = "".localtime(time());
$local =~ s/:/\\:/g;

for ($i=0;$i<$GRUNS;$i++) {
RRDp::cmd "graph $PNG ", "--title 'Test GRAPH' ",
	"--imgformat PNG --height 150 --vertical-label 'Dummy Units' ".
	"--start now".(-$RUNS*$STEP),
	"--color ARROW#bfbfbf",
        "DEF:alpha=$RRD:in:AVERAGE",
        "DEF:beta=$RRD:out:AVERAGE",
        "CDEF:calc=alpha,beta,+,1.5,/",
        "AREA:alpha#0022e9:Alpha",
        "STACK:beta#00b871:Beta",
        "STACK:calc#ff0091:Calc\\j",
	"PRINT:alpha:AVERAGE:'Average Alpha\\: %1.2lf %S'",
	"PRINT:alpha:MIN:'Min Alpha\\: %1.2lf %S'",
	"PRINT:alpha:MAX:'Max Alpha\\: %1.2lf %S'",
	"GPRINT:calc:AVERAGE:'Average calc\\: %1.2lf %S\\r'",
	"GPRINT:calc:MIN:'Min calc\\: %1.2lf %S'",
	"GPRINT:calc:MAX:'Max calc\\: %1.2lf %S'",
        "VRULE:".($now-3600)."#008877:'60 Minutes ago'",
        "COMMENT:'\\s'",
        "COMMENT:'Graph created on\\: $local\\c'";

$answer = RRDp::read;
}
($user2,$sys2,$real2) =  ($RRDp::user,$RRDp::sys,$RRDp::real);

print "ANSWER:\n$$answer";

printf "\n-- average Time for one PNG Graph\n".
       "   usr/grf: %1.5fs sys/grf: %1.5fs real/grf: %1.5fs".
       "  graphs/sec: %1.2f\n\n",
  ($user2-$user1)/$GRUNS, 
  ($sys2-$sys1)/$GRUNS, 
  ($real2-$real1)/$GRUNS, 
  $GRUNS/($real2-$real1);

RRDp::end;
