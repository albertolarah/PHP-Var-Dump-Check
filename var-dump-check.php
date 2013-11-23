<?php
use JakubOnderka\PhpVarDumpCheck;

const SUCCESS = 0,
    WITH_ERRORS = 1,
    FAILED = 255;

if (PHP_VERSION < '5.3.2') {
    die("PHP Var Dump Check require PHP 5.3.2 and newer");
}

function showOptions()
{
?>
Options:
    -e <ext>    Check only files with selected extensions separated by comma
                (default: php,php3,php4,php5,phtml)
    --exclude   Exclude directory. If you want exclude multiple directory, use
                multiple exclude parameters.
    --no-colors Disable colors in console output.
    -h, --help  Print this help.
<?php
}

/**
 * Help
 */
if (!isset($_SERVER['argv'][1]) || in_array('-h', $_SERVER['argv']) || in_array('--help', $_SERVER['argv'])) { ?>
PHP Var Dump check version 0.1
---------------------------
Usage:
    var-dump-check [-e ext] [--no-colors] [--exclude dir] [files or directories]
<?php
    showOptions();
    exit;
}


$files = array(
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/vendor/autoload.php'
);

$autoloadFileFound = false;
foreach ($files as $file) {
    if (file_exists($file)) {
        require $file;
        $autoloadFileFound = true;
        break;
    }
}

if (!$autoloadFileFound) {
    die(
      'You need to set up the project dependencies using the following commands:' . PHP_EOL .
      'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
      'php composer.phar install' . PHP_EOL
    );
}

try {
    $settings = PhpVarDumpCheck\Settings::parseArguments($_SERVER['argv']);
} catch (PhpVarDumpCheck\Exception\InvalidArgument $e) {
    echo "Invalid option {$e->getArgument()}" . PHP_EOL . PHP_EOL;
    showOptions();
    die(FAILED);
}

try {
    $check = new PhpVarDumpCheck\Manager();
    $status = $check->check($settings);
    die($status ? SUCCESS : WITH_ERRORS);
} catch (PhpVarDumpCheck\Exception\Exception $e) {
    echo $e->getMessage();
    die(FAILED);
}