/*
 * This file was generated automatically by ExtUtils::ParseXS version 3.28 from the
 * contents of RRDs.xs. Do not edit this file, edit RRDs.xs instead.
 *
 *    ANY CHANGES MADE HERE WILL BE LOST!
 *
 */

#line 1 "RRDs.xs"
#ifdef __cplusplus
extern "C" {
#endif

#include "EXTERN.h"
#include "perl.h"
#include "XSUB.h"

#ifdef __cplusplus
}
#endif

/*
 * rrd_tool.h includes config.h, but at least on Ubuntu Breezy Badger
 * 5.10 with gcc 4.0.2, the C preprocessor picks up Perl's config.h
 * which is included from the Perl includes and never reads rrdtool's
 * config.h.  Without including rrdtool's config.h, this module does
 * not compile, so include it here with an explicit path.
 *
 * Because rrdtool's config.h redefines VERSION which is originally
 * set via Perl's Makefile.PL and passed down to the C compiler's
 * command line, save the original value and reset it after the
 * includes.
 */
#define VERSION_SAVED VERSION
#undef VERSION
#include "../../rrd_config.h"
#include "../../src/rrd_tool.h"
#undef VERSION
#define VERSION VERSION_SAVED
#undef VERSION_SAVED

/* perl 5.004 compatibility */
#if PERLPATCHLEVEL < 5 
#define PL_sv_undef sv_undef
#endif


#define rrdcode(name) \
		argv = (char **) malloc((items+1)*sizeof(char *));\
		argv[0] = "dummy";\
		for (i = 0; i < items; i++) { \
		    STRLEN len; \
		    char *handle= SvPV(ST(i),len);\
		    /* actually copy the data to make sure possible modifications \
		       on the argv data does not backfire into perl */ \
		    argv[i+1] = (char *) malloc((strlen(handle)+1)*sizeof(char)); \
		    strcpy(argv[i+1],handle); \
 	        } \
		rrd_clear_error();\
		RETVAL=name(items+1,argv); \
		for (i=0; i < items; i++) {\
		    free(argv[i+1]);\
		} \
		free(argv);\
		\
		if (rrd_test_error()) XSRETURN_UNDEF;

#define hvs(VAL) hv_store_ent(hash, sv_2mortal(newSVpv(data->key,0)),VAL,0)		    

#define rrdinfocode(name) \
		/* prepare argument list */ \
		argv = (char **) malloc((items+1)*sizeof(char *)); \
		argv[0] = "dummy"; \
		for (i = 0; i < items; i++) { \
		    STRLEN len; \
		    char *handle= SvPV(ST(i),len); \
		    /* actually copy the data to make sure possible modifications \
		       on the argv data does not backfire into perl */ \
		    argv[i+1] = (char *) malloc((strlen(handle)+1)*sizeof(char)); \
		    strcpy(argv[i+1],handle); \
 	        } \
                rrd_clear_error(); \
                data=name(items+1, argv); \
                for (i=0; i < items; i++) { \
		    free(argv[i+1]); \
		} \
		free(argv); \
                if (rrd_test_error()) XSRETURN_UNDEF; \
                hash = newHV(); \
   	        save=data; \
                while (data) { \
		/* the newSV will get copied by hv so we create it as a mortal \
           to make sure it does not keep hanging round after the fact */ \
		    switch (data->type) { \
		    case RD_I_VAL: \
			if (isnan(data->value.u_val)) \
			    hvs(&PL_sv_undef); \
			else \
			    hvs(newSVnv(data->value.u_val)); \
			break; \
			case RD_I_INT: \
			hvs(newSViv(data->value.u_int)); \
			break; \
		    case RD_I_CNT: \
			hvs(newSViv(data->value.u_cnt)); \
			break; \
		    case RD_I_STR: \
			hvs(newSVpv(data->value.u_str,0)); \
			break; \
		    case RD_I_BLO: \
			hvs(newSVpv(data->value.u_blo.ptr,data->value.u_blo.size)); \
			break; \
		    } \
		    data = data->next; \
	        } \
            rrd_info_free(save); \
            RETVAL = newRV_noinc((SV*)hash);

/*
 * should not be needed if libc is linked (see ntmake.pl)
#ifdef WIN32
 #define free free
 #define malloc malloc
 #define realloc realloc
#endif
*/


#line 130 "RRDs.c"
#ifndef PERL_UNUSED_VAR
#  define PERL_UNUSED_VAR(var) if (0) var = var
#endif

#ifndef dVAR
#  define dVAR		dNOOP
#endif


/* This stuff is not part of the API! You have been warned. */
#ifndef PERL_VERSION_DECIMAL
#  define PERL_VERSION_DECIMAL(r,v,s) (r*1000000 + v*1000 + s)
#endif
#ifndef PERL_DECIMAL_VERSION
#  define PERL_DECIMAL_VERSION \
	  PERL_VERSION_DECIMAL(PERL_REVISION,PERL_VERSION,PERL_SUBVERSION)
#endif
#ifndef PERL_VERSION_GE
#  define PERL_VERSION_GE(r,v,s) \
	  (PERL_DECIMAL_VERSION >= PERL_VERSION_DECIMAL(r,v,s))
#endif
#ifndef PERL_VERSION_LE
#  define PERL_VERSION_LE(r,v,s) \
	  (PERL_DECIMAL_VERSION <= PERL_VERSION_DECIMAL(r,v,s))
#endif

/* XS_INTERNAL is the explicit static-linkage variant of the default
 * XS macro.
 *
 * XS_EXTERNAL is the same as XS_INTERNAL except it does not include
 * "STATIC", ie. it exports XSUB symbols. You probably don't want that
 * for anything but the BOOT XSUB.
 *
 * See XSUB.h in core!
 */


/* TODO: This might be compatible further back than 5.10.0. */
#if PERL_VERSION_GE(5, 10, 0) && PERL_VERSION_LE(5, 15, 1)
#  undef XS_EXTERNAL
#  undef XS_INTERNAL
#  if defined(__CYGWIN__) && defined(USE_DYNAMIC_LOADING)
#    define XS_EXTERNAL(name) __declspec(dllexport) XSPROTO(name)
#    define XS_INTERNAL(name) STATIC XSPROTO(name)
#  endif
#  if defined(__SYMBIAN32__)
#    define XS_EXTERNAL(name) EXPORT_C XSPROTO(name)
#    define XS_INTERNAL(name) EXPORT_C STATIC XSPROTO(name)
#  endif
#  ifndef XS_EXTERNAL
#    if defined(HASATTRIBUTE_UNUSED) && !defined(__cplusplus)
#      define XS_EXTERNAL(name) void name(pTHX_ CV* cv __attribute__unused__)
#      define XS_INTERNAL(name) STATIC void name(pTHX_ CV* cv __attribute__unused__)
#    else
#      ifdef __cplusplus
#        define XS_EXTERNAL(name) extern "C" XSPROTO(name)
#        define XS_INTERNAL(name) static XSPROTO(name)
#      else
#        define XS_EXTERNAL(name) XSPROTO(name)
#        define XS_INTERNAL(name) STATIC XSPROTO(name)
#      endif
#    endif
#  endif
#endif

/* perl >= 5.10.0 && perl <= 5.15.1 */


/* The XS_EXTERNAL macro is used for functions that must not be static
 * like the boot XSUB of a module. If perl didn't have an XS_EXTERNAL
 * macro defined, the best we can do is assume XS is the same.
 * Dito for XS_INTERNAL.
 */
#ifndef XS_EXTERNAL
#  define XS_EXTERNAL(name) XS(name)
#endif
#ifndef XS_INTERNAL
#  define XS_INTERNAL(name) XS(name)
#endif

/* Now, finally, after all this mess, we want an ExtUtils::ParseXS
 * internal macro that we're free to redefine for varying linkage due
 * to the EXPORT_XSUB_SYMBOLS XS keyword. This is internal, use
 * XS_EXTERNAL(name) or XS_INTERNAL(name) in your code if you need to!
 */

#undef XS_EUPXS
#if defined(PERL_EUPXS_ALWAYS_EXPORT)
#  define XS_EUPXS(name) XS_EXTERNAL(name)
#else
   /* default to internal */
#  define XS_EUPXS(name) XS_INTERNAL(name)
#endif

#ifndef PERL_ARGS_ASSERT_CROAK_XS_USAGE
#define PERL_ARGS_ASSERT_CROAK_XS_USAGE assert(cv); assert(params)

/* prototype to pass -Wmissing-prototypes */
STATIC void
S_croak_xs_usage(const CV *const cv, const char *const params);

STATIC void
S_croak_xs_usage(const CV *const cv, const char *const params)
{
    const GV *const gv = CvGV(cv);

    PERL_ARGS_ASSERT_CROAK_XS_USAGE;

    if (gv) {
        const char *const gvname = GvNAME(gv);
        const HV *const stash = GvSTASH(gv);
        const char *const hvname = stash ? HvNAME(stash) : NULL;

        if (hvname)
	    Perl_croak_nocontext("Usage: %s::%s(%s)", hvname, gvname, params);
        else
	    Perl_croak_nocontext("Usage: %s(%s)", gvname, params);
    } else {
        /* Pants. I don't think that it should be possible to get here. */
	Perl_croak_nocontext("Usage: CODE(0x%"UVxf")(%s)", PTR2UV(cv), params);
    }
}
#undef  PERL_ARGS_ASSERT_CROAK_XS_USAGE

#define croak_xs_usage        S_croak_xs_usage

#endif

/* NOTE: the prototype of newXSproto() is different in versions of perls,
 * so we define a portable version of newXSproto()
 */
#ifdef newXS_flags
#define newXSproto_portable(name, c_impl, file, proto) newXS_flags(name, c_impl, file, proto, 0)
#else
#define newXSproto_portable(name, c_impl, file, proto) (PL_Sv=(SV*)newXS(name, c_impl, file), sv_setpv(PL_Sv, proto), (CV*)PL_Sv)
#endif /* !defined(newXS_flags) */

#if PERL_VERSION_LE(5, 21, 5)
#  define newXS_deffile(a,b) Perl_newXS(aTHX_ a,b,file)
#else
#  define newXS_deffile(a,b) Perl_newXS_deffile(aTHX_ a,b)
#endif

#line 274 "RRDs.c"

XS_EUPXS(XS_RRDs_error); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_error)
{
    dVAR; dXSARGS;
    if (items != 0)
       croak_xs_usage(cv,  "");
    {
	SV *	RETVAL;
#line 134 "RRDs.xs"
		if (! rrd_test_error()) XSRETURN_UNDEF;
                RETVAL = newSVpv(rrd_get_error(),0);
#line 287 "RRDs.c"
	RETVAL = sv_2mortal(RETVAL);
	ST(0) = RETVAL;
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_last); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_last)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 144 "RRDs.xs"
      int i;
      char **argv;
#line 304 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 147 "RRDs.xs"
              rrdcode(rrd_last);
#line 309 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_first); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_first)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 155 "RRDs.xs"
      int i;
      char **argv;
#line 325 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 158 "RRDs.xs"
              rrdcode(rrd_first);
#line 330 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_create); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_create)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 167 "RRDs.xs"
        int i;
	char **argv;
#line 346 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 170 "RRDs.xs"
		rrdcode(rrd_create);
	        RETVAL = 1;
#line 352 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_update); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_update)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 180 "RRDs.xs"
        int i;
	char **argv;
#line 368 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 183 "RRDs.xs"
		rrdcode(rrd_update);
       	        RETVAL = 1;
#line 374 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_tune); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_tune)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 193 "RRDs.xs"
        int i;
	char **argv;
#line 390 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 196 "RRDs.xs"
		rrdcode(rrd_tune);
       	        RETVAL = 1;
#line 396 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_graph); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_graph)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    PERL_UNUSED_VAR(ax); /* -Wall */
    SP -= items;
    {
#line 206 "RRDs.xs"
	char **calcpr=NULL;
	int i,xsize,ysize;
	double ymin,ymax;
	char **argv;
	AV *retar;
#line 417 "RRDs.c"
	SV *	RETVAL;
#line 212 "RRDs.xs"
		argv = (char **) malloc((items+1)*sizeof(char *));
		argv[0] = "dummy";
		for (i = 0; i < items; i++) { 
		    STRLEN len;
		    char *handle = SvPV(ST(i),len);
		    /* actually copy the data to make sure possible modifications
		       on the argv data does not backfire into perl */ 
		    argv[i+1] = (char *) malloc((strlen(handle)+1)*sizeof(char));
		    strcpy(argv[i+1],handle);
 	        }
		rrd_clear_error();
		rrd_graph(items+1,argv,&calcpr,&xsize,&ysize,NULL,&ymin,&ymax); 
		for (i=0; i < items; i++) {
		    free(argv[i+1]);
		}
		free(argv);

		if (rrd_test_error()) {
			if(calcpr)
			   for(i=0;calcpr[i];i++)
				rrd_freemem(calcpr[i]);
			XSRETURN_UNDEF;
		}
		retar=newAV();
		if(calcpr){
			for(i=0;calcpr[i];i++){
				 av_push(retar,newSVpv(calcpr[i],0));
				 rrd_freemem(calcpr[i]);
			}
			rrd_freemem(calcpr);
		}
		EXTEND(sp,4);
		PUSHs(sv_2mortal(newRV_noinc((SV*)retar)));
		PUSHs(sv_2mortal(newSViv(xsize)));
		PUSHs(sv_2mortal(newSViv(ysize)));
#line 455 "RRDs.c"
	PUTBACK;
	return;
    }
}


XS_EUPXS(XS_RRDs_fetch); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_fetch)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    PERL_UNUSED_VAR(ax); /* -Wall */
    SP -= items;
    {
#line 252 "RRDs.xs"
		time_t        start,end;		
		unsigned long step, ds_cnt,i,ii;
		rrd_value_t   *data,*datai;
		char **argv;
		char **ds_namv;
		AV *retar,*line,*names;
#line 477 "RRDs.c"
	SV *	RETVAL;
#line 259 "RRDs.xs"
		argv = (char **) malloc((items+1)*sizeof(char *));
		argv[0] = "dummy";
		for (i = 0; i < items; i++) { 
		    STRLEN len;
		    char *handle= SvPV(ST(i),len);
		    /* actually copy the data to make sure possible modifications
		       on the argv data does not backfire into perl */ 
		    argv[i+1] = (char *) malloc((strlen(handle)+1)*sizeof(char));
		    strcpy(argv[i+1],handle);
 	        }
		rrd_clear_error();
		rrd_fetch(items+1,argv,&start,&end,&step,&ds_cnt,&ds_namv,&data); 
		for (i=0; i < items; i++) {
		    free(argv[i+1]);
		}
		free(argv);
		if (rrd_test_error()) XSRETURN_UNDEF;
                /* convert the ds_namv into perl format */
		names=newAV();
		for (ii = 0; ii < ds_cnt; ii++){
		    av_push(names,newSVpv(ds_namv[ii],0));
		    rrd_freemem(ds_namv[ii]);
		}
		rrd_freemem(ds_namv);			
		/* convert the data array into perl format */
		datai=data;
		retar=newAV();
		for (i = start+step; i <= end; i += step){
			line = newAV();
			for (ii = 0; ii < ds_cnt; ii++){
 			  av_push(line,(isnan(*datai) ? &PL_sv_undef : newSVnv(*datai)));
			  datai++;
			}
			av_push(retar,newRV_noinc((SV*)line));
		}
		rrd_freemem(data);
		EXTEND(sp,5);
		PUSHs(sv_2mortal(newSViv(start+step)));
		PUSHs(sv_2mortal(newSViv(step)));
		PUSHs(sv_2mortal(newRV_noinc((SV*)names)));
		PUSHs(sv_2mortal(newRV_noinc((SV*)retar)));
#line 521 "RRDs.c"
	PUTBACK;
	return;
    }
}


XS_EUPXS(XS_RRDs_times); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_times)
{
    dVAR; dXSARGS;
    if (items != 2)
       croak_xs_usage(cv,  "start, end");
    PERL_UNUSED_VAR(ax); /* -Wall */
    SP -= items;
    {
	char *	start = (char *)SvPV_nolen(ST(0))
;
	char *	end = (char *)SvPV_nolen(ST(1))
;
#line 306 "RRDs.xs"
		rrd_time_value_t start_tv, end_tv;
		char    *parsetime_error = NULL;
		time_t	start_tmp, end_tmp;
#line 545 "RRDs.c"
	SV *	RETVAL;
#line 310 "RRDs.xs"
		rrd_clear_error();
		if ((parsetime_error = rrd_parsetime(start, &start_tv))) {
			rrd_set_error("start time: %s", parsetime_error);
			XSRETURN_UNDEF;
		}
		if ((parsetime_error = rrd_parsetime(end, &end_tv))) {
			rrd_set_error("end time: %s", parsetime_error);
			XSRETURN_UNDEF;
		}
		if (rrd_proc_start_end(&start_tv, &end_tv, &start_tmp, &end_tmp) == -1) {
			XSRETURN_UNDEF;
		}
		EXTEND(sp,2);
		PUSHs(sv_2mortal(newSVuv(start_tmp)));
		PUSHs(sv_2mortal(newSVuv(end_tmp)));
#line 563 "RRDs.c"
	PUTBACK;
	return;
    }
}


XS_EUPXS(XS_RRDs_xport); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_xport)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    PERL_UNUSED_VAR(ax); /* -Wall */
    SP -= items;
    {
#line 330 "RRDs.xs"
                time_t start,end;		
                int xsize;
		unsigned long step, col_cnt,row_cnt,i,ii;
		rrd_value_t *data,*ptr;
                char **argv,**legend_v;
		AV *retar,*line,*names;
#line 585 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 337 "RRDs.xs"
		argv = (char **) malloc((items+1)*sizeof(char *));
		argv[0] = "dummy";
		for (i = 0; i < items; i++) { 
		    STRLEN len;
		    char *handle = SvPV(ST(i),len);
		    /* actually copy the data to make sure possible modifications
		       on the argv data does not backfire into perl */ 
		    argv[i+1] = (char *) malloc((strlen(handle)+1)*sizeof(char));
		    strcpy(argv[i+1],handle);
 	        }
		rrd_clear_error();
		rrd_xport(items+1,argv,&xsize,&start,&end,&step,&col_cnt,&legend_v,&data); 
		for (i=0; i < items; i++) {
		    free(argv[i+1]);
		}
		free(argv);
		if (rrd_test_error()) XSRETURN_UNDEF;

                /* convert the legend_v into perl format */
		names=newAV();
		for (ii = 0; ii < col_cnt; ii++){
		    av_push(names,newSVpv(legend_v[ii],0));
		    rrd_freemem(legend_v[ii]);
		}
		rrd_freemem(legend_v);			

		/* convert the data array into perl format */
		ptr=data;
		retar=newAV();
		for (i = start+step; i <= end; i += step){
			line = newAV();
			for (ii = 0; ii < col_cnt; ii++){
 			  av_push(line,(isnan(*ptr) ? &PL_sv_undef : newSVnv(*ptr)));
			  ptr++;
			}
			av_push(retar,newRV_noinc((SV*)line));
		}
		rrd_freemem(data);

		EXTEND(sp,7);
		PUSHs(sv_2mortal(newSViv(start+step)));
		PUSHs(sv_2mortal(newSViv(end)));
		PUSHs(sv_2mortal(newSViv(step)));
		PUSHs(sv_2mortal(newSViv(col_cnt)));
		PUSHs(sv_2mortal(newRV_noinc((SV*)names)));
		PUSHs(sv_2mortal(newRV_noinc((SV*)retar)));
#line 635 "RRDs.c"
	PUTBACK;
	return;
    }
}


XS_EUPXS(XS_RRDs_info); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_info)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 388 "RRDs.xs"
		rrd_info_t *data,*save;
                int i;
                char **argv;
		HV *hash;
#line 653 "RRDs.c"
	SV *	RETVAL;
#line 393 "RRDs.xs"
		rrdinfocode(rrd_info);	
#line 657 "RRDs.c"
	RETVAL = sv_2mortal(RETVAL);
	ST(0) = RETVAL;
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_updatev); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_updatev)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 401 "RRDs.xs"
		rrd_info_t *data,*save;
                int i;
                char **argv;
		HV *hash;
#line 676 "RRDs.c"
	SV *	RETVAL;
#line 406 "RRDs.xs"
		rrdinfocode(rrd_update_v);	
#line 680 "RRDs.c"
	RETVAL = sv_2mortal(RETVAL);
	ST(0) = RETVAL;
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_graphv); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_graphv)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 414 "RRDs.xs"
		rrd_info_t *data,*save;
                int i;
                char **argv;
		HV *hash;
#line 699 "RRDs.c"
	SV *	RETVAL;
#line 419 "RRDs.xs"
		rrdinfocode(rrd_graph_v);	
#line 703 "RRDs.c"
	RETVAL = sv_2mortal(RETVAL);
	ST(0) = RETVAL;
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_dump); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_dump)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 427 "RRDs.xs"
        int i;
       char **argv;
#line 720 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 430 "RRDs.xs"
               rrdcode(rrd_dump);
                       RETVAL = 1;
#line 726 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_restore); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_restore)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 439 "RRDs.xs"
        int i;
       char **argv;
#line 742 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 442 "RRDs.xs"
               rrdcode(rrd_restore);
                       RETVAL = 1;
#line 748 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}


XS_EUPXS(XS_RRDs_flushcached); /* prototype to pass -Wmissing-prototypes */
XS_EUPXS(XS_RRDs_flushcached)
{
    dVAR; dXSARGS;
    PERL_UNUSED_VAR(cv); /* -W */
    {
#line 451 "RRDs.xs"
	int i;
	char **argv;
#line 764 "RRDs.c"
	int	RETVAL;
	dXSTARG;
#line 454 "RRDs.xs"
		rrdcode(rrd_flushcached);
#line 769 "RRDs.c"
	XSprePUSH; PUSHi((IV)RETVAL);
    }
    XSRETURN(1);
}

#ifdef __cplusplus
extern "C"
#endif
XS_EXTERNAL(boot_RRDs); /* prototype to pass -Wmissing-prototypes */
XS_EXTERNAL(boot_RRDs)
{
#if PERL_VERSION_LE(5, 21, 5)
    dVAR; dXSARGS;
#else
    dVAR; dXSBOOTARGSXSAPIVERCHK;
#endif
#if (PERL_REVISION == 5 && PERL_VERSION < 9)
    char* file = __FILE__;
#else
    const char* file = __FILE__;
#endif

    PERL_UNUSED_VAR(file);

    PERL_UNUSED_VAR(cv); /* -W */
    PERL_UNUSED_VAR(items); /* -W */
#if PERL_VERSION_LE(5, 21, 5)
    XS_VERSION_BOOTCHECK;
#  ifdef XS_APIVERSION_BOOTCHECK
    XS_APIVERSION_BOOTCHECK;
#  endif
#endif

        newXS_deffile("RRDs::error", XS_RRDs_error);
        (void)newXSproto_portable("RRDs::last", XS_RRDs_last, file, "@");
        (void)newXSproto_portable("RRDs::first", XS_RRDs_first, file, "@");
        (void)newXSproto_portable("RRDs::create", XS_RRDs_create, file, "@");
        (void)newXSproto_portable("RRDs::update", XS_RRDs_update, file, "@");
        (void)newXSproto_portable("RRDs::tune", XS_RRDs_tune, file, "@");
        (void)newXSproto_portable("RRDs::graph", XS_RRDs_graph, file, "@");
        (void)newXSproto_portable("RRDs::fetch", XS_RRDs_fetch, file, "@");
        newXS_deffile("RRDs::times", XS_RRDs_times);
        (void)newXSproto_portable("RRDs::xport", XS_RRDs_xport, file, "@");
        (void)newXSproto_portable("RRDs::info", XS_RRDs_info, file, "@");
        (void)newXSproto_portable("RRDs::updatev", XS_RRDs_updatev, file, "@");
        (void)newXSproto_portable("RRDs::graphv", XS_RRDs_graphv, file, "@");
        (void)newXSproto_portable("RRDs::dump", XS_RRDs_dump, file, "@");
        (void)newXSproto_portable("RRDs::restore", XS_RRDs_restore, file, "@");
        (void)newXSproto_portable("RRDs::flushcached", XS_RRDs_flushcached, file, "@");

    /* Initialisation Section */

#line 123 "RRDs.xs"
#ifdef MUST_DISABLE_SIGFPE
	signal(SIGFPE,SIG_IGN);
#endif
#ifdef MUST_DISABLE_FPMASK
	fpsetmask(0);
#endif 

#line 830 "RRDs.c"

    /* End of Initialisation Section */

#if PERL_VERSION_LE(5, 21, 5)
#  if PERL_VERSION_GE(5, 9, 0)
    if (PL_unitcheckav)
        call_list(PL_scopestack_ix, PL_unitcheckav);
#  endif
    XSRETURN_YES;
#else
    Perl_xs_boot_epilog(aTHX_ ax);
#endif
}

