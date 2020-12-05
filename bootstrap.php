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

$twig_loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/app/views');
$twig = new \Twig\Environment($twig_loader, [
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'cache' => APP_PATH . '/app/cache',
]);
if($_ENV['APP_DEBUG'] ?? false)
{
    $twig->addExtension(new \Twig\Extension\DebugExtension());
}

session_name('__lotgd_improved');
session_start();

class DB
{
    private static $instances = [];
    private $connection = null;
    private $query = null;
    private $error = false;

    public static function getInstance()
    {
        $class = static::class;

        if(!isset(self::$instances[$class]))
        {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }

    public function __construct()
    {
        try
        {
            $this->connection = new PDO(
                $_ENV['DB_DSN'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
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

    public function doQuery($statement, $values = null)
    {
        $this->query = $this->connection->prepare($statement);

        try
        {
            $this->query->execute($values);
        }
        catch(\Exception $e)
        {
            $this->error = $e;
        }

        return $this;
    }

    public function clear()
    {
        $this->query = null;
        $this->error = null;

        return $this;
    }

    public function getNext($method = null)
    {
        return $this->query->fetch($method ?? PDO::FETCH_OBJ);
    }

    public function getAll($method = null)
    {
        return $this->query->fetchAll($method ?? PDO::FETCH_OBJ);
    }

    public function getCount()
    {
        return $this->query->fetchColumn();
    }

    public function getRows()
    {
        return $this->query->rowCount();
    }

    public function getError()
    {
        return $this->error ?? false;
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

$db = DB::getInstance();

// Check if the SQL file was imported
$tables = $db->doQuery('show tables')->getRows();
$db->clear();
if($tables == 0)
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
    $superuser = $db->doQuery("select count(*) from `accounts` where `superuser` & 1")->getCount();
    $db->clear();
    if($superuser == 0)
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
$db = null;
