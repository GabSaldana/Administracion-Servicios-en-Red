/*****************************************************************************
 * RRDtool 1.4.5  Copyright by Tobi Oetiker, 1997-2010
 *****************************************************************************
 * rrdupdate.c  Main program for the (standalone) rrdupdate utility
 *****************************************************************************
 * $Id: rrdupdate.c 2161 2010-12-26 19:24:48Z oetiker $
 *****************************************************************************/

#if defined(_WIN32) && !defined(__CYGWIN__) && !defined(__CYGWIN32__) && !defined(HAVE_CONFIG_H)
#include "../win32/config.h"
#else
#ifdef HAVE_CONFIG_H
#include "../rrd_config.h"
#endif
#endif

#include "rrd.h"

int main(
    int argc,
    char **argv)
{
    rrd_update(argc, argv);
    if (rrd_test_error()) {
        printf("RRDtool " PACKAGE_VERSION
               "  Copyright by Tobi Oetiker, 1997-2010\n\n"
               "Usage: rrdupdate filename\n"
               "\t\t\t[--template|-t ds-name:ds-name:...]\n"
               "\t\t\ttime|N:value[:value...]\n\n"
               "\t\t\tat-time@value[:value...]\n\n"
               "\t\t\t[ time:value[:value...] ..]\n\n");

        printf("ERROR: %s\n", rrd_get_error());
        rrd_clear_error();
        return 1;
    }
    return 0;
}
