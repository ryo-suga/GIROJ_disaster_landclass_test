@echo off
rem **************************************************************************
rem �N���C�A���g���ɍ��킹�Ĉȉ���ύX
rem (��)
rem set MACRO="C:\Program Files (x86)\teraterm\ttpmacro.exe"
rem set RUN_UPLOAD_SHELL="C:\Program Files (x86)\teraterm\GIROJ_LANDCLASS\setpassword_hon.ttl"

rem ----------------------�ҏW�ӏ�----------------------
rem ttpmacro.exe�̏ꏊ
set MACRO="ttpmacro.exe�t�@�C���̏ꏊ(TeraTerm�C���X�g�[���t�H���_�����ɂ���܂�)"
rem �p�X���[�h�����}�N���̐ݒu�ꏊ
set RUN_UPLOAD_SHELL="���O�C���p�X���[�h�ύX�}�N��(setpassword_xxx.ttl)�̏ꏊ"
rem ----------------------�ҏW�ӏ�----------------------
rem **************************************************************************

%MACRO% %RUN_UPLOAD_SHELL%

echo ���s�I��
pause
exit /b 0
