baseDocumentRoot='/var/www'

username=`whoami`

read -p "Enter your site name (without .com or .dev.com): " siteName

dirName="$baseDocumentRoot/$siteName.com"

if [ ! -d "$dirName" ]; then
	# CREATE DIRECTORY
	sudo mkdir "$dirName" 
fi

if [ -d "$dirName" ]; then
	# DIRECTORY EXISTS

	# copy all files
	sudo cp -R ./ "$dirName"

	# CHANGE USER GROUP & PERMISSIONS FOR ENTIRE SITE
	sudo chown -R root:$username $dirName
	sudo chmod 775 $dirName
	sudo find $dirName -type d -exec chmod 775 {} +
	sudo find $dirName -type f -exec chmod 664 {} +

	# GRANT ACCESS TO APACHE FOR TEMP FOLDER
	sudo chown -R 'www-data':$username "$dirName/temp"
	sudo find "$dirName/temp" -type d -exec chmod 775 {} +
	sudo find "$dirName/temp" -type f -exec chmod 664 {} + 

	echo "Site directories created..."

	serverName="$siteName.dev.com"

	# CREATE VIRTUAL HOST CONFIG FILE IN HOME FOLDER
	cd ~
	touch $serverName
	
	echo "# Virtual Host configuration for $serverName
<VirtualHost *:80>
	ServerName $serverName
	ServerAdmin webmaster@localhost

	DocumentRoot $dirName/public_html
	<Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>
	<Directory $dirName/public_html>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride FileInfo Options
		Order allow,deny
		allow from all
	</Directory>

	ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
	<Directory "/usr/lib/cgi-bin">
		AllowOverride None
		Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
		Order allow,deny
		Allow from all
	</Directory>

	ErrorLog $dirName/temp/debug/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog $dirName/temp/debug/access.log combined
</VirtualHost>" >> $serverName

	# MOVE CONFIG FILE TO PLACE
	sudo mv $serverName /etc/apache2/sites-available/$serverName
fi

echo "Virtual Host configuration complete... Do not forget to make an entry in your HOSTS file!"
echo "Run 'sudo a2ensite $serverName' to enable your virtual host!"
read -p "Press any key to exit..." pK
