@echo off
rem **************************************************************************
rem �N���C�A���g���ɍ��킹�Ĉȉ���ύX
rem (��)
rem set MACRO="C:\Program Files (x86)\teraterm\ttpmacro.exe"
rem set RUN_UPLOAD_SHELL="C:\Program Files (x86)\teraterm\GIROJ_LANDCLASS\runUploadShell.ttl"

rem ttpmacro.exe�̏ꏊ
set MACRO="ttpmacro.exe�t�@�C���̏ꏊ(TeraTerm�C���X�g�[���t�H���_�����ɂ���܂�)"
rem �A�b�v���[�h�}�N���̐ݒu�ꏊ
set RUN_UPLOAD_SHELL="�^���f�[�^�A�b�v���[�h�}�N��(runUploadShell.ttl)�̏ꏊ"
rem **************************************************************************

set CONFIG=config_test.ttl

%MACRO% %RUN_UPLOAD_SHELL% %CONFIG%

echo ���s�I��
pause
exit /b 0
