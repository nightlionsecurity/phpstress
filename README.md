phpstress
=========

Night Lion Security proof of concept denial of service / stress tester for PHP websites running with Apache and NGINX systems (PHP-FPM and PHP-CGI)

Using a standard cable/DSL connection, this attack can flood a Linux web server’s CPU and RAM using standard HTTP requests. This attack effects Apache or NGINX web servers that handle dynamic PHP content using either PHP-CGI or PHP-FPM (which includes WordPress websites). In addition, the requests made by the attack (or default) web server configurations will continue to keep the server’s resources in use far past the end of the attack. 

To execute the attack, set your target URL and time delay parameters and the script will do the rest. 

For more information, visit: https://www.nightlionsecurity.com/blog/news/2014/04/phpstress-dos-attack-php-nginx-apache/
