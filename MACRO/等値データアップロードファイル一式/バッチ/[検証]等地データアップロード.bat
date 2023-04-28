@echo off
rem **************************************************************************
rem クライアント環境に合わせて以下を変更
rem (例)
rem set MACRO="C:\Program Files (x86)\teraterm\ttpmacro.exe"
rem set RUN_UPLOAD_SHELL="C:\Program Files (x86)\teraterm\GIROJ_LANDCLASS\runUploadShell.ttl"

rem ttpmacro.exeの場所
set MACRO="ttpmacro.exeファイルの場所(TeraTermインストールフォルダ直下にあります)"
rem アップロードマクロの設置場所
set RUN_UPLOAD_SHELL="型式データアップロードマクロ(runUploadShell.ttl)の場所"
rem **************************************************************************

set CONFIG=config_test.ttl

%MACRO% %RUN_UPLOAD_SHELL% %CONFIG%

echo 実行終了
pause
exit /b 0
