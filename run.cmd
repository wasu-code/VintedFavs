cd public

:: Start XAMPP
xampp_start

:: Start PHP server
start "PHP Server" cmd /c "php -S localhost:8000"

:: Open Browser
start "" "http://localhost:8000"

:: Pause the current command window
echo Do you want to close XAMPP?
pause

:: Stop XAMPP
xampp_stop
