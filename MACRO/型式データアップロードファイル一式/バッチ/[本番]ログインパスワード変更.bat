@echo off
rem **************************************************************************
rem クライアント環境に合わせて以下を変更

rem ttpmacro.exeの場所
set MACRO="ttpmacro.exeファイルの場所(TeraTermインストールフォルダ直下にあります)"
rem パスワード生成マクロの設置場所
set RUN_UPLOAD_SHELL="ログインパスワード変更マクロ(setpassword_xxx.ttl)の場所"
rem **************************************************************************

%MACRO% %RUN_UPLOAD_SHELL%

echo 実行終了
pause
exit /b 0
