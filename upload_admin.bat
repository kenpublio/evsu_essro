@echo off
echo ========================================
echo   Uploading Admin Folder
echo ========================================
echo.
echo Uploading all admin files...
echo.

scp -i C:\Users\ACER\.ssh\hostinger_deploy -P 65002 -r admin u656591888@145.79.14.190:/home/u656591888/domains/steelblue-baboon-493940.hostingersite.com/public_html/

echo.
echo ========================================
echo   Admin Upload Complete!
echo ========================================
echo.
pause