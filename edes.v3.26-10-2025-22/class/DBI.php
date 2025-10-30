<?php
class DBI {
private static $connections = [];
private static $currentConnection = null;
private static $lastStatement = null;
private static $defaultConfigs = [
'mysql' => ['port' => 3306, 'charset' => 'utf8mb4'],
'mysqli' => ['port' => 3306, 'charset' => 'utf8mb4'],
'pgsql' => ['port' => 5432, 'encoding' => 'UTF8'],
'oracle' => ['port' => 1521, 'service_name' => 'ORCL'],
'informix' => ['port' => 9088, 'log' => 'ON', 'mode' => 'ANSI']
];
public static function checkService($host, $port = null, $driver = 'mysql') {
$driver = strtolower($driver);
$port = $port ?: (isset(self::$defaultConfigs[$driver]['port']) ? self::$defaultConfigs[$driver]['port'] : null);
if (!$port) {
throw new Exception("Puerto no definido para el driver: $driver");
}
try {
$dsn = self::buildServiceDSN($driver, $host, $port);
$pdo = new PDO($dsn, 'invalid_user', 'invalid_password', [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_TIMEOUT => 3,
PDO::ATTR_PERSISTENT => false
]);
return true;
} catch (PDOException $e) {
$errorCode = $e->getCode();
$errorMessage = strtolower($e->getMessage());
$activePatterns = [
'access denied',
'authentication failed',
'invalid password',
'invalid credentials',
'ora-01017',
'ora-28000',
'password authentication failed',
'authorization failure'
];
foreach ($activePatterns as $pattern) {
if (strpos($errorMessage, $pattern) !== false) {
return true;
}
}
return false;
} catch (Exception $e) {
return false;
}
}
private static function buildServiceDSN($driver, $host, $port) {
switch ($driver) {
case 'mysql':
case 'mysqli':
return "mysql:host=$host;port=$port;charset=utf8mb4";
case 'pgsql':
return "pgsql:host=$host;port=$port";
case 'oracle':
return "oci:dbname=//$host:$port/ORCL";
case 'informix':
return "informix:host=$host; service=$port";
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function open($config) {
$driver = strtolower(isset($config['driver']) ? $config['driver'] : 'mysql');
$host = isset($config['host']) ? $config['host'] : 'localhost';
$dbname = isset($config['database']) ? $config['database'] : '';
$username = isset($config['username']) ? $config['username'] : '';
$password = isset($config['password']) ? $config['password'] : '';
$port = isset($config['port']) ? $config['port'] : (isset(self::$defaultConfigs[$driver]['port']) ? self::$defaultConfigs[$driver]['port'] : null);
try {
$dsn = self::buildDSN($driver, $host, $dbname, $port, $config);
$options = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => true
];
$pdo = new PDO($dsn, $username, $password, $options);
self::$connections[$driver] = $pdo;
self::$currentConnection = $driver;
return $pdo;
} catch (PDOException $e) {
throw new Exception("Error de conexión: " . $e->getMessage());
}
}
public static function checkDatabaseExists($dbname, $driver) {
$query = self::getQuerySchema($dbname, $driver);
self::query($query);
$result = self::get('num');
return !empty($result) && (isset($result[0]) && $result[0] == $dbname || isset($result['SCHEMA_NAME']) && $result['SCHEMA_NAME'] == $dbname);
}
public static function query($sql, $params = []) {
try {
$pdo = self::getConnection();
self::$lastStatement = $pdo->prepare($sql);
return self::$lastStatement->execute($params);
} catch (PDOException $e) {
throw new Exception("Error en query: " . $e->getMessage());
}
}
public static function get($fetchMode = 'assoc') {
if (!self::$lastStatement) {
return [];
}
$fetchStyle = PDO::FETCH_ASSOC;
if ($fetchMode === 'num') {
$fetchStyle = PDO::FETCH_NUM;
} elseif ($fetchMode === 'both') {
$fetchStyle = PDO::FETCH_BOTH;
} elseif ($fetchMode === 'object') {
$fetchStyle = PDO::FETCH_OBJ;
}
return self::$lastStatement->fetchAll($fetchStyle);
}
public static function getQuerySchema($dbname, $driver) {
$driver = strtolower($driver);
switch ($driver) {
case 'mysql':
case 'mysqli':
return "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
case 'pgsql':
return "SELECT datname FROM pg_database WHERE datname = '$dbname'";
case 'oracle':
return "SELECT username FROM all_users WHERE username = UPPER('$dbname')";
case 'informix':
return "SELECT dbsname FROM systables WHERE tabid = 1 AND dbsname = '$dbname'";
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function getQueryDrop($dbname, $driver) {
$driver = strtolower($driver);
switch ($driver) {
case 'mysql':
case 'mysqli':
return "DROP DATABASE IF EXISTS `$dbname`";
case 'pgsql':
return "DROP DATABASE IF EXISTS \"$dbname\"";
case 'oracle':
return "DROP USER $dbname CASCADE";
case 'informix':
return "DROP DATABASE '$dbname'";
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function getCreateDB($dbname, $driver, $options = []) {
$driver = strtolower($driver);
switch ($driver) {
case 'mysql':
case 'mysqli':
return "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
case 'pgsql':
return "CREATE DATABASE \"$dbname\" ENCODING 'UTF8'";
case 'oracle':
$password = isset($options['password']) ? $options['password'] : 'password123';
return "CREATE USER $dbname IDENTIFIED BY $password DEFAULT TABLESPACE USERS QUOTA UNLIMITED ON USERS";
case 'informix':
return "CREATE DATABASE '$dbname' WITH LOG";
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function count($table) {
$pdo = self::getConnection();
$driver = self::$currentConnection;
switch ($driver) {
case 'mysql':
case 'mysqli':
case 'pgsql':
case 'informix':
$query = "SELECT COUNT(*) as total FROM $table";
break;
case 'oracle':
$query = "SELECT COUNT(*) as total FROM $table";
break;
default:
throw new Exception("Driver no soportado: $driver");
}
self::query($query);
$result = self::get('num');
return (int) (isset($result[0][0]) ? $result[0][0] : 0);
}
public static function tableExists($tableName) {
$pdo = self::getConnection();
$driver = self::$currentConnection;
$dbname = self::getCurrentDatabase();
switch ($driver) {
case 'mysql':
case 'mysqli':
$query = "SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$tableName'";
break;
case 'pgsql':
$query = "SELECT tablename FROM pg_tables
WHERE schemaname = 'public' AND tablename = '$tableName'";
break;
case 'oracle':
$query = "SELECT table_name FROM all_tables
WHERE owner = UPPER('$dbname') AND table_name = UPPER('$tableName')";
break;
case 'informix':
$query = "SELECT tabname FROM systables
WHERE tabname = '$tableName'";
break;
default:
throw new Exception("Driver no soportado: $driver");
}
self::query($query);
$result = self::get('num');
return !empty($result);
}
public static function createTestTable($tableName = 'prueba') {
$driver = self::$currentConnection;
switch ($driver) {
case 'mysql':
case 'mysqli':
$sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
`id` INT AUTO_INCREMENT PRIMARY KEY,
`nombre` VARCHAR(50) NOT NULL,
`email` VARCHAR(100) NOT NULL UNIQUE,
`fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
break;
case 'pgsql':
$sql = "CREATE TABLE IF NOT EXISTS \"$tableName\" (
id SERIAL PRIMARY KEY,
nombre VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
break;
case 'oracle':
$sql = "CREATE TABLE $tableName (
id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
nombre VARCHAR2(50) NOT NULL,
email VARCHAR2(100) NOT NULL UNIQUE,
fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
break;
case 'informix':
$sql = "CREATE TABLE $tableName (
id SERIAL PRIMARY KEY,
nombre VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
fecha_creacion DATETIME YEAR TO FRACTION(5) DEFAULT CURRENT YEAR TO FRACTION(5)
)";
break;
default:
throw new Exception("Driver no soportado: $driver");
}
return self::query($sql);
}
public static function insertTestData($tableName = 'prueba') {
$sql = "INSERT INTO $tableName (nombre, email) VALUES
('Juan Pérez'	, 'juan@example.com' ),
('María García'	, 'maria@example.com')";
return self::query($sql);
}
public static function dropTable($tableName) {
$driver = self::$currentConnection;
switch ($driver) {
case 'mysql':
case 'mysqli':
$sql = "DROP TABLE IF EXISTS `$tableName`";
break;
case 'pgsql':
$sql = "DROP TABLE IF EXISTS \"$tableName\"";
break;
case 'oracle':
$sql = "DROP TABLE $tableName";
break;
case 'informix':
$sql = "DROP TABLE $tableName";
break;
default:
throw new Exception("Driver no soportado: $driver");
}
return self::query($sql);
}
public static function getTableCollation($tableName) {
$pdo = self::getConnection();
$driver = self::$currentConnection;
$dbname = self::getCurrentDatabase();
switch ($driver) {
case 'mysql':
case 'mysqli':
$query = "SELECT TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = '$dbname'
AND TABLE_NAME = '$tableName'";
self::query($query);
$result = self::get('assoc');
return isset($result[0]['TABLE_COLLATION']) ? $result[0]['TABLE_COLLATION'] : '';
case 'pgsql':
$query = "SELECT pg_encoding_to_char(encoding) as encoding
FROM pg_database
WHERE datname = '$dbname'";
self::query($query);
$result = self::get('assoc');
return isset($result[0]['encoding']) ? $result[0]['encoding'] : '';
default:
return 'N/A';
}
}
public static function getCurrentDatabase() {
$pdo = self::getConnection();
$driver = self::$currentConnection;
try {
switch ($driver) {
case 'mysql':
case 'mysqli':
$result = $pdo->query("SELECT DATABASE()")->fetch(PDO::FETCH_NUM);
return $result[0] ?? '';
case 'pgsql':
$result = $pdo->query("SELECT CURRENT_DATABASE()")->fetch(PDO::FETCH_NUM);
return $result[0] ?? '';
case 'oracle':
$result = $pdo->query("SELECT USER FROM DUAL")->fetch(PDO::FETCH_NUM);
return $result[0] ?? '';
case 'informix':
$result = $pdo->query("SELECT DBINFO('dbname') FROM systables WHERE tabid = 1")->fetch(PDO::FETCH_NUM);
return $result[0] ?? '';
default:
return '';
}
} catch (Exception $e) {
return '';
}
}
public static function closeAll() {
foreach (self::$connections as $driver => $pdo) {
self::$connections[$driver] = null;
}
self::$currentConnection = null;
self::$lastStatement = null;
}
public static function createSchema($schemaName, $driver, $options = []) {
$driver = strtolower($driver);
switch ($driver) {
case 'mysql':
case 'mysqli':
return self::getCreateDB($schemaName, $driver, $options);
case 'pgsql':
$schemaName = pg_escape_string($schemaName);
return "CREATE SCHEMA IF NOT EXISTS \"$schemaName\"";
case 'oracle':
return "CREATE TABLESPACE $schemaName DATAFILE '$schemaName.dbf' SIZE 100M AUTOEXTEND ON";
case 'informix':
return self::getCreateDB($schemaName, $driver, $options);
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function schemaExists($schemaName, $driver) {
$driver = strtolower($driver);
switch ($driver) {
case 'mysql':
case 'mysqli':
return self::checkDatabaseExists($schemaName, $driver);
case 'pgsql':
$query = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = '$schemaName'";
self::query($query);
$result = self::get('num');
return !empty($result);
case 'oracle':
$query = "SELECT tablespace_name FROM user_tablespaces WHERE tablespace_name = UPPER('$schemaName')";
self::query($query);
$result = self::get('num');
return !empty($result);
case 'informix':
return self::checkDatabaseExists($schemaName, $driver);
default:
throw new Exception("Driver no soportado: $driver");
}
}
public static function createDatabaseWithSchemas($dbname, $drive, $additionalSchema = 'suid', $options = []) {
try {
echo "Creando base de datos principal '$dbname'...\n";
$createDbQuery = self::getCreateDB($dbname, $drive, $options);
if (!self::query($createDbQuery)) {
throw new Exception("Error al crear base de datos principal");
}
echo "✓ Base de datos '$dbname' creada\n";
if (in_array($drive, ['mysql', 'mysqli', 'pgsql', 'informix'])) {
$config = [
'driver' => $drive,
'host' => 'localhost', // ajustar según configuración
'database' => $dbname,
'username' => $options['username'] ?? '',
'password' => $options['password'] ?? '',
'port' => $options['port'] ?? null
];
self::open($config);
}
if( empty($additionalSchema) ){
return;
}
echo "Creando esquema adicional '$additionalSchema'...\n";
if ($drive === 'pgsql') {
$createSchemaQuery = self::createSchema($additionalSchema, $drive);
if (!self::query($createSchemaQuery)) {
throw new Exception("Error al crear schema adicional");
}
echo "✓ Schema '$additionalSchema' creado\n";
} elseif (in_array($drive, ['oracle'])) {
$createSchemaQuery = self::createSchema($additionalSchema, $drive);
if (!self::query($createSchemaQuery)) {
throw new Exception("Error al crear tablespace adicional");
}
echo "✓ Tablespace '$additionalSchema' creado\n";
} else {
$createSchemaQuery = self::getCreateDB($additionalSchema, $drive, $options);
if (!self::query($createSchemaQuery)) {
throw new Exception("Error al crear base de datos adicional");
}
echo "✓ Base de datos adicional '$additionalSchema' creada\n";
}
return true;
} catch (Exception $e) {
throw new Exception("Error en createDatabaseWithSchemas: " . $e->getMessage());
}
}
public static function createDatabaseStructure($dbname, $schema="", $drive, $options = []) {
$drive = strtolower($drive);
switch ($drive) {
case 'mysql':
case 'mysqli':
self::query(self::getCreateDB($dbname, $drive, $options));
if( !empty($schema) ){
self::query(self::getCreateDB($schema, $drive, $options));
}
break;
case 'pgsql':
self::query(self::getCreateDB($dbname, $drive, $options));
$config = $options;
$config['database'] = $dbname;
self::open($config);
if( !empty($schema) ){
self::query("CREATE SCHEMA IF NOT EXISTS {$schema}");
}
break;
case 'oracle':
self::query(self::getCreateDB($dbname, $drive, $options));
if( !empty($schema) ){
self::query("CREATE TABLESPACE suid DATAFILE '{$schema}.dbf' SIZE 100M AUTOEXTEND ON");
}
break;
case 'informix':
self::query(self::getCreateDB($dbname, $drive, $options));
if( !empty($schema) ){
self::query(self::getCreateDB($schema, $drive, $options));
}
break;
}
return true;
}
private static function buildDSN($driver, $host, $dbname, $port, $config) {
switch ($driver) {
case 'mysql':
case 'mysqli':
return "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
case 'pgsql':
return "pgsql:host=$host;port=$port;dbname=$dbname";
case 'oracle':
$serviceName = isset($config['service_name']) ? $config['service_name'] : 'ORCL';
return "oci:dbname=//$host:$port/$serviceName";
case 'informix':
return "informix:host=$host; service=$port; database=$dbname;";
default:
throw new Exception("Driver no soportado: $driver");
}
}
private static function getConnection() {
if (!self::$currentConnection || !isset(self::$connections[self::$currentConnection])) {
throw new Exception("No hay conexión activa");
}
return self::$connections[self::$currentConnection];
}
public static function setCharset($charset) {
$pdo = self::getConnection();
$driver = self::$currentConnection;
switch ($driver) {
case 'mysql':
case 'mysqli':
return $pdo->exec("SET NAMES $charset") !== false;
case 'pgsql':
return $pdo->exec("SET CLIENT_ENCODING TO '$charset'") !== false;
case 'oracle':
case 'informix':
return true;
default:
return false;
}
}
public static function getCurrentCharset() {
$pdo = self::getConnection();
$driver = self::$currentConnection;
try {
switch ($driver) {
case 'mysql':
case 'mysqli':
$result = $pdo->query("SHOW VARIABLES LIKE 'character_set_connection'")->fetch();
return isset($result['Value']) ? $result['Value'] : '';
case 'pgsql':
$result = $pdo->query("SHOW client_encoding")->fetch();
return isset($result['client_encoding']) ? $result['client_encoding'] : '';
default:
return 'UTF-8';
}
} catch (Exception $e) {
return 'Unknown';
}
}
}