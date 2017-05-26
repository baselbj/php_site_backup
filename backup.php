<?php
/*
* @Name: php Site backup
*
* @Author:
* Basel Juma
* bjuma@shamconsultancy.com
*
* @Copyright 2017 Basel Juma
*
* Description:  simple full site daily backup. using this script a daily backup will be taken
 * and save for week, each day will be save in a separate folder.
 * a backup folder will contain a cope of both database(gzip) and site files(zip)
*
*
* Requirements: PHP5 or above, mysql 5
*/

// Make sure the script can handle large folders/files
ini_set('max_execution_time', 3600);
ini_set('memory_limit','1024M');

/*
 *
 * Configuration
 *
 */

// path to the site to files
$SitePath = "/home/username/public_html/";

// backup folder ... better not to be in public_html
$BackupPath ="/home/username/backup/";

// Database configuration
$DBUSER="zawajzon_edbuser";
$DBPASSWD="zawajzone123$";
$DATABASE="zawajzon_e_db_1";

// folders to exclude
$exclude=[
    '/home/username/public_html/xyz',
];

/*
 * Step one:
 * make sure backup folder exists, if not create one
 * then make sure day backup folder exists, if not create
 */

// Check if backup folder exists
if ( ! is_dir($BackupPath) ) {
    mkdir($BackupPath, 0777, true); //create folder
}

$day= date("D");
// Make Day folder if not exists
if ( ! is_dir($BackupPath."$day/") ) {
    mkdir($BackupPath."$day/" , 0777, true); //create folder
}

// change backup folder to day
$BackupPath .= "$day/";
addMessageLine('start site backup');

// clear log file if exists
$log = $BackupPath . "backupLog.txt";
if(file_exists($log)){
    unlink($log);
}

/*
 * Step two:
 * make site backup by looping all folder files and sub files
 */

// Make site Backup
addMessageLine('start site backup');
Zip($SitePath,$BackupPath."site.zip");
addMessageLine('site backup done');
addMessageLine('backup done');

/*
 * Step three:
 * make database backup using commandline
 */

// Make db backup
$DBFile = $BackupPath . "db.gz";
addMessageLine('start database backup');
db_backup($DBUSER,$DBPASSWD,$DATABASE,$DBFile);
addMessageLine('database backup done');


/*
 * db_backup:
 * database backup function
 */

function db_backup($DBUSER,$DBPASSWD,$DATABASE,$DBFilename)
{
    $cmd = "mysqldump -u $DBUSER --password=$DBPASSWD $DATABASE | gzip --best > $DBFilename";
    passthru($cmd);
}

/*
 * Zip:
 * site backup function,zipping
 */

function Zip($source, $destination)
{
    global $exclude;
    if (!extension_loaded('zip') || !file_exists($source)) {
        addMessageLine( "zip extension is a must");
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE|ZipArchive::OVERWRITE)) {
        addMessageLine( "Can't open backup file: $destination");
        return false;
    }
    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator(
                    $source,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                function ($files, $key, $iterator) use ($exclude) {

                    return !(in_array($files->getPath(). "/" .$files->getFilename(), $exclude));
                }
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            if (is_dir($file) === true)
            {
                addMessageLine("zipping: $file");
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }
    return $zip->close();
}

/*
 * addMessageLine:
 * add message to log file
 */

function addMessageLine($m){
    global  $BackupPath;

    $log = $BackupPath . "backupLog.txt";
    $message = $m . " ... " . date("Y-m-d H:i:s") ."\r\n";
    echo $message;
    file_put_contents($log, $message.PHP_EOL , FILE_APPEND | LOCK_EX);
}
?>
