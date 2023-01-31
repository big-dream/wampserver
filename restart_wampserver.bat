@echo off
wampmanager.exe -quit -id={wampserver64}
ping -n 1 -w 3000 127.255.255.255 > nul
wampmanager.exe
exit
