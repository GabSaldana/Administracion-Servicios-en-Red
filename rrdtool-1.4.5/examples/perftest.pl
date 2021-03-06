#! /usr/bin/perl
#
# $Id:$
#
# Created By Tobi Oetiker <tobi@oetiker.ch>
# Date 2006-10-27
#
#makes programm work AFTER install

my $Chunk = shift @ARGV || 10000;

use lib qw( ../bindings/perl-shared/blib/lib ../bindings/perl-shared/blib/arch /opt/rrdtool-1.4.5/lib/perl );

print <<NOTE;

RRDtool Performance Tester
--------------------------
Running on $RRDs::VERSION;

RRDtool update performance is ultimately disk-bound. Since very little data
does actually get written to disk in a single update, the performance
is highly dependent on the cache situation of your machine.

This test tries to cater for this. It works like this:

1) Create $Chunk RRD files in a tree

2) For $Chunk -> Update RRD file, Sync

3) goto 1)

The numbers at the start of the row, show which
RRA is being updated. So if several RRAs are being updated,
you should see a slowdown as data has to be read from disk.

The growning number in the second column shows how many RRD have been
updated ... If everything is in cache, the number will Jump to $Chunk almost
immediately. Then the system will seem to hang as 'sync' runs, to make sure
all data has been written to disk prior to the next perftest run. This may
not be 100% real-life, so you may want to remove the sync just for fun
(then it is even less real-life, but different)

NOTE

use strict;
use Time::HiRes qw(time);
use RRDs;
use IO::File;
use Time::HiRes qw( usleep );

sub create($$){
  my $file = shift;
  my $time = shift;
  my $start = time; #since we loaded HiRes
  RRDs::create  ( $file.".rrd", "-b$time", qw(
			-s300                        
		        DS:in:GAUGE:400:U:U
		        DS:out:GAUGE:400:U:U
		        RRA:AVERAGE:0.5:1:600
		        RRA:AVERAGE:0.5:6:600
		        RRA:MAX:0.5:6:600
		        RRA:AVERAGE:0.5:24:600
		        RRA:MAX:0.5:24:600
		        RRA:AVERAGE:0.5:144:600
		        RRA:MAX:0.5:144:600
		));
   my $total = time - $start;
   my $error =  RRDs::error;
   die $error if $error;
   return $total;
}

sub update($$){
  my $file = shift;
  my $time = shift;
  my $in = rand(1000);
  my $out = rand(1000);
  my $start = time;
  my $ret = RRDs::updatev($file.".rrd", $time.":$in:$out");
  my $total = time - $start;
  my $error =  RRDs::error;
  die $error if $error;
  return $total;
}

sub tune($){
  my $file = shift;
  my $start = time;
  RRDs::tune ($file.".rrd", "-a","in:U","-a","out:U","-d","in:GAUGE","-d","out:GAUGE");
  my $total = time - $start;
  my $error =  RRDs::error;
  die $error if $error;
  return $total;
}

sub infofetch($){
  my $file = shift;
  my $start = time;
  my $info = RRDs::info ($file.".rrd");
  my $error =  RRDs::error;
  die $error if $error;
  my $lasttime =  $info->{last_update} - $info->{last_update} % $info->{step};           
  my $fetch = RRDs::fetch ($file.".rrd",'AVERAGE','-s',$lasttime-1,'-e',$lasttime);
  my $total = time - $start;
  my $error =  RRDs::error;
  die $error if $error;
  return $total;
}

sub stddev ($$$){ #http://en.wikipedia.org/wiki/Standard_deviation
  my $sum = shift;
  my $squaresum = shift;
  my $count = shift;
  return sqrt( 1 / $count * ( $squaresum - $sum*$sum / $count ))
}

sub makerrds($$$$){
    my $count = shift;
    my $total = shift;
    my $list = shift;
    my $time = shift;
    my @files;
    my $now = int(time);
    for (1..$count){
        my $id = sprintf ("%07d",$total);
        $id =~ s/^(.)(.)(.)(.)(.)//;
        push @$list, "$1/$2/$3/$4/$5/$id";    
        -d "$1" or mkdir "$1";
        -d "$1/$2" or mkdir "$1/$2";
        -d "$1/$2/$3" or mkdir "$1/$2/$3";
        -d "$1/$2/$3/$4" or mkdir "$1/$2/$3/$4";
        -d "$1/$2/$3/$4/$5" or mkdir "$1/$2/$3/$4/$5";
	push @files, $list->[$total];
        create $list->[$total++],$time-2;
	if ($now < int(time)){
	  $now = int(time);
	  print STDERR "Creating RRDs: ", $count - $_," rrds to go. \r";
        }
    }
    return $count;
}
 
sub main (){
    mkdir "db-$$" or die $!;
    chdir "db-$$";

    my $step = $Chunk; # number of rrds to creat for every round
    
    my @path;
    my $time=int(time);

    my $tracksize = 0;
    my $uppntr = 0;

    
    my %squaresum = ( cr => 0, up => 0 );
    my %sum = ( cr => 0, up => 0 );
    my %count =( cr => 0, up => 0 );

    my $printtime = time;
    my %step;
    for (qw(1 6 24 144)){
          $step{$_} = int($time / 300 / $_);
    }
    
    for (0..2) {
        # enhance the track
        $time += 300;
        $tracksize += makerrds $step,$tracksize,\@path,$time;            
        # run benchmark
    
        for (0..50){
      	    $time += 300;
            my $count = 0;
            my $sum = 0;
            my $squaresum = 0;
            my $prefix = "";
            for (qw(1 6 24 144)){
                if (int($time / 300 / $_) > $step{$_})  {
                    $prefix .= "$_  ";
                    $step{$_} = int($time / 300 / $_);
                 }
                 else {
                    $prefix .= (" " x length("$_")) . "  ";
                 }   
            }
            my $now = int(time);
            for (my $i = 0; $i<$tracksize;$i ++){
               my $ntime = int(time);
               if ($now < $ntime or $i == $tracksize){
                   printf STDERR "$prefix %7d \r",$i;
                   $now = $ntime;
               }
               my $elapsed = update($path[$i],$time);                
               $sum += $elapsed;
               $squaresum += $elapsed**2;
               $count++;
            };
            my $startsync = time;
            print STDERR 's';
            system "sync";
            print STDERR "\h";
            my $synctime = time-$startsync;     
            $sum += $synctime;
            $squaresum += $synctime**2;
            my $ups = $count/$sum;
            my $sdv = stddev($sum,$squaresum,$count);
            printf STDERR "$prefix %7d %6.0f Up/s (%6.5f sdv)\n",$count,$ups,$sdv;
        }
	print STDERR "\n";
    }
}

main;
