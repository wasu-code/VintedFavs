:: Start XAMPP
D:/xampp/xampp_start.exe

:: Start PHP server
start "PHP Server" cmd /c "php -S localhost:8000"

:: Open Browser
start "" "localhost:8000"

:: Pause the current command window
echo Do you want to close XAMPP?
pause

:: Stop XAMPP
D:/xampp/xampp_stop.exe
