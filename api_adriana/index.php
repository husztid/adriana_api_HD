<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler("hibakezelo::handle_error");
set_exception_handler("hibakezelo::handle_exception");

header("Content-type: application/json; charset=UTF-8");

$parts = explode("/", $_SERVER['REQUEST_URI']);

# ha nem az ügyfél táblát kérnék le, error
if ( $parts[2] != "customers" ){
    http_response_code(404);
    exit;
}

$id = $parts[3] ?? null;

# db adatok
$server = "localhost";
$tableName = "api_data";
$userName = "root";
$jelszo = "";

# db kapcsolat
$database = new Database($server, $tableName, $userName, $jelszo);
$gateway = new CustomerGateway($database);
$controller = new CustomerController($gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
