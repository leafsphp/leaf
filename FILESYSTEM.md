# Leaf FS
Leaf FS simply allows you to manipulate filesystems much simpler than what you're used to.
## Importing FS
`$fs = new Leaf\Core\FS('base directory');`

## Base directory
The base directory is considered as the working directory for Leaf FS. Most methods utilise the base directory, therefore we have provided functions to manipulate the base directory however you see fit.

### Set base directory
This sets the base directory.It can also be used to change the base directory if it is already set.
```php
$fs->setBaseDirectory('directory path');
```

### Get base directory
If at a point you want to find out the current base directory, you can use the `getBaseDirectory` method.
```php
echo $fs->getBaseDirectory();
```

## Creating a new file
```
$fs->createFile("filename");
```
Creates file in the base directory.

## Creating a new folder
There are 3 ways to create a new folder
Create a folder in the base directory
```php
$fs->mkDirInBase("folder name");
```

Create a folder insome random path
```php
$fs->mkDir("folder path");
```

## Renaming directories
```php
$fs->renameDir("directory path");
```
Rename directory

## Free space
```php
$fs->freeSpace("directory path");
```

## Creating a file
```php
$fs->createFile("filename");
```
This creates a new file in the base directory


## writing to a file
```php
$fs->writeFile("filename", "content to write to file");
```
Replaces content in a file i the base directory


## appending to a file
```php
$fs->appendFile("filename");
```
This creates a new file in the base directory