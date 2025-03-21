# Temporary directory creator and watcher

### Initial setup

You need to set existed writeable working directories where temporary directories will be created.
Also, you can set minimum free space to be available to use certain working directory.
If there are no working directories with available space - exception will be thrown

```php
<?php
//get instance of watcher 
$watcher=\S4urp8n\TempDirectory\Watcher::getInstance();

//set working directories
$watcher->addWorkingDirectory('/path/to/directory')
        ->addWorkingDirectory('/path/to/directory2');
//or
$watcher->setWorkingDirectories(['/path/to/directory2']); 
        
//set minimum free space
$watcher->setMinimumSpaceAvailableInDirectory(20000); 

?>
```

### Manual creation of temporary directories

This code created a unique empty directory in one of the working directory you previously set

```php
<?php

$watcher=\S4urp8n\TempDirectory\Watcher::getInstance();
$expiredInMinutes=60;
$fullPathToTemporaryDirectory=$watcher->createTempDirectory('somename', $expiredInMinutes);

?>
```

### Deletion of manual created directory

You can manually delete the created folder using its full path when you finish using it

```php
<?php
$watcher=\S4urp8n\TempDirectory\Watcher::getInstance();
$watcher->removeDirectory("/path to driectory");
?>
```

### Deletion in cron or script

You can run deletion of all expired temp directories using this code
```php
<?php
$watcher=\S4urp8n\TempDirectory\Watcher::getInstance();
$watcher->clearExpired();
?>
```