***********************************************************************
**  v1.7.5 PSpice Models Library - TI NexFET Power N/P Channel FET's
*  (C) Copyright 2011 Texas Instruments Incorporated. All rights reserved.
***********************************************************************
***********************************************************************
* Notice:
* The data and models provided herein are intended to be used by 
* customers as an aid in designing electrical circuits using 
* semiconductor devices manufactured &/or sold by Texas Instruments.
* The models and associated information contained herein are not to
* be construed as a guarantee of electrical performance and/or device 
* specification.  Therefore, Texas Instruments does not assume any 
* liability arising out of the use of said data or models, nor from
* the devices or products manufactured therefrom.  Texas Instruments
* does not convey any license under its patent rights nor the rights
* of others.  Texas Instruments reserves the right to change models 
* without prior notice.  
*
* TI is a registered trademark of Texas Instruments Inc.
*************************************************************************
********************************************************************************
*
* Released by: Analog e-Lab Design Center, Texas Instruments Inc.
* Part: CSD17510Q5A
* Date: 2/2/2010
* Model Type: TRANSIENT
* Simulator: PSPICE
* Datasheet: SLPS271D - July 2010 - Revised November 2010
*
*****************************************************************************
*$
**********************************************************************
**********************************************************************
*                                                                    *
* CSD17510q5a   -  PSpice Model for use with Cadence OrCAD/Allegro   *
*                                                                    *
**********************************************************************
* SUBCKT Definition:  1=D  2=G  3=S
**********************************************************************
.SUBCKT CSD17510Q5A  1 2 3
.PARAM  ptrc1   4.00e-3  
.PARAM  ptrc2   8.50e-6
.PARAM  pwidthP 1.4291
.PARAM  pwidth  {pwidthP}
.PARAM  perimP  {2.1*pwidthP}
.PARAM  perim   {2.1*pwidth}
.PARAM  pkgL    1.0
M1   10 11 12 12  NMOS	W={pwidth} L=0.5u  PD={perim}  PS={perim}
DBD   8  7	 DBD
RDB   8  7   1e10
CGD0  5  7	 {pkgL*1E-12 + 1e-15}
CGS0  5  8	 {pkgL*2e-12 + 1e-15}
CDS0 10 12	 {pkgL*5e-12 + 1e-15}
M2   12 11 12 20  PMOSd W={pwidthP} L=0.125u PD={perimP} PS={perimP}
DDx  20 10   DDx
DDG  14  5   DD
DGD  14 10   DD
* Note:  gate oxide capacitance included in NMOS below
CGS   5 13	 120E-12
RGS  13  8	 80
LGG   2  5	 {pkgL*0.95e-9 + 0.04e-9}
RGG   5 11	 {pkgL*0.28 + 0.8}
RSB  12  9	 RTEMP 0.30E-3
RSS   9  8 	 0.03e-3
RSP   8  6 	 {pkgL*0.47e-3 + 0.02e-3}
LSS   6  3	 {pkgL*0.65E-9 + 0.02e-9}
RDP   4  7	 {pkgL*0.38e-3 + 0.03e-3}
RDD   7 10 	 RTEMP 1.26e-3
LDD   1  4	 0.05E-9
******************************************************************* 
.MODEL  NMOS   NMOS (    LEVEL  = 7             Version = 3.2
+ TNOM   = 27            LINT   = 0.045e-6      CAPMOD = 2
+ TOX    = 3.30e-8       NCH    = 1.242e17      NSUB   = 1.0e16
+ AGS    = 0.0           PVAG   = 0.0
+ A0     = 1.20          A1     = 0.0           A2     = 0.9
+ UA     = 2.10e-9       UB     = 3.8e-18       VBM    = -5.0
+ UA1    = 2.00e-9       UB1    = -2.0e-18      UTE    = -1.50
+ KT1    = -0.88         KT2    = 0.022         KT1L   = 1.0e-15
+ DVT0   = 2.5           DVT1   = 0.62	      DVT2   = -0.033
+ ETA0   = 0.045         ETAB   = -0.07         XJ     = 4e-7
+ PDIBLC1= 0.08          PDIBLC2= 0.025         NLX    = 1.78e-7
+ PSCBE1 = 6.0e8         PSCBE2 = 1e-7          PCLM   = 1.3
+ VOFF   = -0.23         NFACTOR= 1.5           JS     = 0
+ DROUT  = 0.8           DSUB   = 1.1           DELTA  = 0.03
+ CJ     = 1e-18         CJSW   = 1e-18         CF     = 0
+ CGSO   = 202e-12       CGDO   = 20e-12        CGBO   = 0    )
******************************************************************* 
.MODEL  PMOSd   PMOS (   LEVEL  = 1
+ TNOM   = 27            VTO    = -0.3		IS     = 6.0e-14
+ TOX    = 3.30e-8       NSUB   = 1e18
+ CJ     = 1e-18         CJSW   = 1e-18
+ CGSO   = 1e-18         CGDO   = 1e-18         CGBO   = 1e-18 )
******************************************************************* 
.MODEL DBD D (CJO=2800e-12 VJ=0.80 M=0.530 FC=0.5 TT=2e-09 TNOM=27
+ IS=2.9e-12 N=1.01  RS=1.75e-3 TRS1=3.0e-3 XTI=2.8 BV=33)
.MODEL DDx D (CJO=35e-12 VJ=0.40 M=0.50 IS=1e-12 RS=0.1 TNOM=27)
.MODEL DD  D (CJO=340e-12 VJ=0.50 M=0.90 IS=1e-13 RS=0.1 TNOM=27 BV=31)
******************************************************************* 
.MODEL RTEMP RES (TC1={ptrc1} TC2={ptrc2})
*******************************************************************
.ENDS CSD17510Q5A
*