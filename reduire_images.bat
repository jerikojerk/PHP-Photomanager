
REM on commence par fixer des chemins 
SET PHP_DIR=C:\Program Files (x86)\PHP
SET PHP_PATH=%PHP_DIR%\php7.1.3
SET PHP_SCRIPT=%PHP_DIR%\scripts\PhotoManager
REM et on lance le script
echo "Debut traitement"

"%PHP_PATH%\php.exe" -c "%PHP_PATH%\php-script.ini" -f "%PHP_SCRIPT%\reduire_images.php"  > reduire_images.log
echo "merci"


REM "%PHP_PATH%\php-win.exe" -c "%PHP_PATH%\php-script.ini" -f "%PHP_SCRIPT%\reduire_images.php" --hauteur 800  > reduire_images.log
