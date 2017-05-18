# PHP Site Backup
This script is a simple full site daily backup. By using this script a daily backup will be taken and saved for a week, each day will be save in a separate folder.
 
The backup folder will contain a cope of both database(gzip) and site files(zip).

##How to use
* Open script file and configure your database, backup folder, site folder, exclusions.
* Although you can run the script from your browser it is better to use it as a cron job for more information about how to make a cron job in CPanel please refer to (https://documentation.cpanel.net/display/ALD/Cron+Jobs). 
* It is more safe to make the backup out of the public_html.

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem, or question can be discussed.

## License

This project is licensed under the terms of the [MIT License] (https://opensource.org/licenses/MIT).
