<?php

$script = pathinfo($_SERVER['SCRIPT_NAME'])['filename'];

define('APP_PATH', __DIR__);
define('WEB_PATH', APP_PATH . '/public');

require_once APP_PATH . '/vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

function error_page($header, $code, $message)
{
    include WEB_PATH . '/error.php';
    exit();
}

try
{
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
catch(\Exception $e)
{
    error_page(
        $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
        500,
        'Could not load the .env file.'
    );
}

session_name('__lotgd_improved');
session_start();

class DB
{
    private static $connection;
    private static $query;
    private static $result;
    private static $error;

    private static function createConnection()
    {
        try
        {
            self::$connection = new PDO(
                $_ENV['DB_DSN'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        }
        catch(\Exception $e)
        {
            /** @todo Handle other errors. */
            $messages = [
                2002 => 'The host supplied in the .env file could not be reached.',
                1049 => 'The database supplied in the .env file could not be found.',
                1698 => 'The username and/or password supplied in the .env file did not work.',
            ];

            error_page(
                $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
                500,
                $messages[$e->getCode()] ?? 'An unknown error occurred.'
            );
        }
    }

    private static function init()
    {
        if(!self::$connection)
        {
            self::createConnection();
        }
    }

    private static function clear()
    {
        self::$query = null;
        self::$error = null;

        return;
    }

    public static function doQuery($statement, $values = null)
    {
        self::init();
        self::clear();

        self::$query = self::$connection->prepare($statement);

        try
        {
            self::$query->execute($values);
        }
        catch(\Exception $e)
        {
            self::$error = $e;
        }

        return __CLASS__;
    }

    public static function getNext($method = null)
    {
        return self::$query->fetch($method ?? PDO::FETCH_OBJ);
    }

    public static function getAll($method = null)
    {
        return self::$query->fetchAll($method ?? PDO::FETCH_OBJ);
    }

    public static function getCount()
    {
        return self::$query->fetchColumn();
    }

    public static function getNumberOfRows()
    {
        return self::$query->rowCount();
    }

    public static function getError()
    {
        return self::$error ?? false;
    }

    public static function end()
    {
        self::clear();

        return;
    }
}

class Template
{
    public static $parser;
    public static $values;

    private static function createParser()
    {
        $twig_loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/app/views');
        $twig = new \Twig\Environment($twig_loader, [
            'debug' => $_ENV['APP_DEBUG'] ?? false,
            'cache' => APP_PATH . '/app/cache',
        ]);
        if($_ENV['APP_DEBUG'] ?? false)
        {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        self::$parser = $twig;
    }

    private static function init()
    {
        if(!self::$parser)
        {
            self::createParser();
        }

        if(!is_array(self::$values))
        {
            self::$values = [];
        }
    }

    public static function load($file)
    {
        self::init();

        return self::$parser->load($file);
    }

    public static function render($file, $values = [])
    {
        self::init();

        return self::$parser->render($file, $values);
    }
}

function request($key, $filled = false)
{
    if
    (
        isset($_REQUEST[$key])
        &&
        (
            !$filled
            ||
            (
                $filled
                &&
                !empty($_REQUEST[$key])
            )
        )
    )
    {
        return empty($_REQUEST[$key])
            ? true
            : $_REQUEST[$key]
        ;
    }

    return null;
}

function session($key, $value = null)
{
    if(!$value && isset($_SESSION[$key]))
    {
        return $_SESSION[$key];
    }

    if($value)
    {
        $_SESSION[$key] = $value;
    }

    return null;
}

// Check if the SQL file was imported
DB::doQuery('show tables');
if(DB::getNumberOfRows() == 0)
{
    error_page(
        $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
        500,
        'SQL file has not been imported yet.'
    );
}

// Check if there's a superuser already, if not in the installer/error page
if($script != 'new_installer' && $script != 'error')
{
    DB::doQuery("select count(*) from `accounts` where `superuser` & 1");
    if(DB::getNumberOfRows() == 0)
    {
        include WEB_PATH . '/new_installer.php';
        exit();
    }
}
else
{
    header('Location:/');
    exit();
}

// If the script makes it here, all prechecks are done
DB::end();
