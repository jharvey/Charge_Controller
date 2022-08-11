set version=V0.2
set base_name=hw48na

set posInputFile=%version%/%base_name%-top-pos.csv
set posOutputFile=%version%/%base_name%_JLC-CPL_%version%.csv
set lib_path=../kicad-libraries

mkdir %version%

call %lib_path%/Fabrication/jlc_prepare_position.bat %posInputFile% %posOutputFile% %lib_path%

