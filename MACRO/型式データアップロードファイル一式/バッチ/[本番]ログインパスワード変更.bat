@echo off
rem **************************************************************************
rem �N���C�A���g���ɍ��킹�Ĉȉ���ύX

rem ttpmacro.exe�̏ꏊ
set MACRO="ttpmacro.exe�t�@�C���̏ꏊ(TeraTerm�C���X�g�[���t�H���_�����ɂ���܂�)"
rem �p�X���[�h�����}�N���̐ݒu�ꏊ
set RUN_UPLOAD_SHELL="���O�C���p�X���[�h�ύX�}�N��(setpassword_xxx.ttl)�̏ꏊ"
rem **************************************************************************

%MACRO% %RUN_UPLOAD_SHELL%

echo ���s�I��
pause
exit /b 0
