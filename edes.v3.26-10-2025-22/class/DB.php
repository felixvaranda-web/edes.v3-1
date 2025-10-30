<?PHP
if( !class_exists('eDes') ){
$_ENV["APP_TRON"] = "";
$_ENV["trace"] = false;
include( dirname(__FILE__)."/support_for_db.php" );
}
class DB {
const TYPE_INTEGER = 'integer';
const TYPE_FLOAT = 'float';
const TYPE_STRING = 'string';
const TYPE_BOOLEAN = 'boolean';
const TYPE_DATETIME = 'datetime';
const TYPE_DATE = 'date';
const TYPE_TIME = 'time';
const TYPE_BINARY = 'binary';
const TYPE_JSON = 'json';
protected static $dbh = null;
protected static $dsn = null;
protected static $driver = null;
protected static $cursor = [];
protected static $tableExists = [];
private static $listClass = [];
private static $tron = false;
private static $suid;
public static function checkService($host, $port=3306){
try{
$dsn = "mysql:host=$host;port={$port}";
$pdo = new PDO($dsn, 'invalid_user', 'invalid_password', [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_TIMEOUT => 3
]);
return true;
}catch( PDOException $e ){
$errorCode = $e->getCode();
return ( $errorCode == 1045 );
}
}
public static function getQuerySchema($dbname, $dbType){
$schema = [
'mysqli'	=> "SELECT SCHEMA_NAME as dbname FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'"
,'pgsql'	=> "SELECT datname as dbname FROM pg_database WHERE datname = '{$dbname}'"
,'oracle'	=> "SELECT username as dbname FROM all_users WHERE username = '".strtoupper($dbname)."'"
,'informix'	=> "SELECT dbsname as dbname FROM systables WHERE tabid = 1 AND dbsname = '{$dbname}'"
];
$schema['mysql'] = $schema['mysqli'];
return $schema[ strtolower($dbType) ];
}
public static function getQueryDrop($dbname, $dbType){
$drop = [
'mysqli' 	=> "DROP DATABASE IF EXISTS {$dbname}"
,'pgsql'	=> "DROP DATABASE IF EXISTS {$dbname}"
,'oracle'	=> "DROP USER {$dbname} CASCADE"
,'informix'	=> "DROP DATABASE $dbname"
];
$drop['mysql'] = $drop['mysqli'];
return $drop[ strtolower($dbType) ];
}
public static function getCreateDB($dbname, $dbType){
$options = [];
$charset = $options['charset'] ?? 'utf8mb4';
$collation = $options['collation'] ?? 'utf8mb4_unicode_ci';
$mysqli = "CREATE DATABASE IF NOT EXISTS `$dbname`
CHARACTER SET $charset
COLLATE $collation";
$encoding = $options['encoding'] ?? 'UTF8';
$locale = $options['locale'] ?? 'en_US.UTF-8';
$pgsql = "CREATE DATABASE \"$dbname\"
ENCODING = '$encoding'
LC_COLLATE = '$locale'
LC_CTYPE = '$locale'";
$password = $options['password'] ?? 'password123';
$tablespace = $options['tablespace'] ?? 'USERS';
$oracle = "CREATE USER $dbname IDENTIFIED BY $password
DEFAULT TABLESPACE $tablespace
QUOTA UNLIMITED ON $tablespace";
$log = $options['log'] ?? 'ON';
$mode = $options['mode'] ?? 'ANSI';
$informix = "CREATE DATABASE '$dbname'
WITH LOG $log
IN $mode MODE";
$create = [
"mysqli"	=> $mysqli
,"mysql"	=> $mysqli
,"pgsql"	=> $pgsql
,"oracle"	=> $oracle
,"informix"	=> $informix
];
return $create[$dbType];
}
public static function open( $hashDB=null ){
self::$tron = $_ENV["trace"];
$sql = self::getSetup( $hashDB );
self::tron("open: ".get_called_class());
try{
if( !in_array(static::class, DB::$listClass) ){
DB::$listClass[] = static::class;
}
if( static::$dbh!=null ){
static::$dbh = null;
}
static::$driver = strtolower($sql["driver"]);
$dsn = self::buildDsn($sql);
$options = self::parseOptions($sql["options"] ?? []);
static::$dbh = new PDO(
$dsn
,$sql["username"]
,$sql["password"]
,$options
);
self::tron("open: {$dsn}");
if( !empty($sql["set"]) ){
for($i=0; $i<count($sql["set"]); $i++){
self::query( $sql["set"][$i] );
}
}
if( !empty($sql["tenant"]) ){
self::tron(">>> entra en openTenant");
self::openTenant($sql["tenant"]);
unset($sql["tenant"]);
return;
}
static::$dbh->beginTransaction();
}catch(PDOException $e){
S::error("", $e->getMessage());
}
}
private static function openTenant($tenant): void {
$check = function( $data ){
if( empty($data) ){
S::error("", "Tenant no encontrado");
}
if( $data["status"]!="A" ){ // [P]ENDING, [A]CTIVE, [S]USPENDED, [D]ELETED, [T]EST, [M]ASTER
S::error("", "Tenant no ACTIVO");
}
if( !empty($data["dt_delete"]) && $data["dt_delete"] <= date("Y-m-d") ){
S::error("", "Tenant con fecha de baja");
}
};
$db = [];
$data = SS::selectOne("select * from gs_tenant where domain={{domain}} or db_path={{db_path}}", [
"domain"  => strtoupper($_SESSION['tenant']),
"db_path" => strtolower($_SESSION['tenant'])
]);
$check($data);
$_SESSION["tenant"] = $data;
foreach($data as $key=>$value){
if( substr($key,0,3)!="db_" ){
continue;
}
$key = substr($key, 3);
if( $key=="statistics" || $key=="development" ){
$_SESSION[$key] = ($value=="Y");
continue;
}
$db[$key] = self::unobfuscate( $value );
if( $key=="options" || $key=="set" ){
$db[$key] = explode("|", $db[$key]);
}
}
if( $db["driver"]=="mysqli" ){
$db["driver"] = "mysql";
}
self::close();
self::open($db);
}
private static function getSetup($db=""){
self::tron("DDBB setup: {$db}");
$tenant = "";
if( isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == 404 ){
$_SERVER['REDIRECT_STATUS'] = null;
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$tenant = explode('/', trim($requestUri, '/'))[0] ?? null;
$tenant = strtoupper(htmlspecialchars($tenant));
$_SESSION['tenant'] = $tenant;
}
if( empty($db) ){
if( !empty($_SESSION["sql"]["driver"]) ){
$db = $_SESSION["sql"];
}else if( !empty(getenv("MAIL_KEYCODE")) ){
$db = self::getMailKeyCode();
}else{
$db = self::getFileIni("../_datos/config/config_db.ini");
}
}else if( gettype($db)=="string" ){
if( strpos($db, "/")===false ){
$db = "../_datos/config/{$db}";
if( strrpos($db, ".") < 3 ){
$db .= ".ini";
}
}
$file = htmlspecialchars($db);
$db = self::getFileIni($file);
}else if( gettype($db)=="array" ){
}
$db["tenant"] = $tenant;
$suid = $db["dbname_sys"] ?? "";
$db['suid']	 	= self::$suid = empty($suid) ? "" : self::unobfuscate($suid).".";
$db['driver']	= self::unobfuscate($db['driver']);
$db['host']		= self::unobfuscate($db['host']);
$db['port']		= self::unobfuscate($db['port'] ?? "");
$db['dbname']	= self::unobfuscate($db['dbname']);
$db['username'] = self::unobfuscate($db['username']);
$db['password'] = self::unobfuscate($db['password']);
if( $db['driver']=="mysqli" ){
$db['driver'] = "mysql";
}
if( !empty($db['set']) && gettype($db['set'])=="string" ){
$db['set'] = [ $db['set'] ];
}
foreach($db['set'] as $key=>$value){
$db['set'][$key] = self::unobfuscate($value);
}
if( !empty($db['options']) && gettype($db['options'])=="string" ){
$db['options'] = [ $db['options'] ];
}
foreach($db['options'] as $key=>$value){
$db['options'][$key] = self::unobfuscate($value);
}
return $db;
}
private static function getFileIni( $file ){
if( !file_exists($file) ){
S::error("File [{$file}] not found");
}
$config = parse_ini_file($file, true);
$env = getenv("APP_ENV");
if( empty($env) ){
$env = $_SERVER['HTTP_HOST'];
if( !isset($config[$env]) ){
$env = gethostname();
}
}
if( !isset($config[$env]) ){
S::error("", "Definición DDBB no encontrada [{$env}]");
}
return $config[$env];
}
private static function buildDsn($config){
switch( $config['driver'] ){
case 'mysql':
return sprintf(
'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
$config['host'],
$config['port'],
$config['dbname']
);
case 'pgsql':
$dsn = "pgsql:host=localhost;dbname=facturacion; options='--client_encoding=UTF8'";
return sprintf(
"pgsql:host=%s;port=%s;dbname=%s;options='--client_encoding=UTF8'",
$config['host'],
$config['port'],
$config['dbname']
);
case 'oci':
return sprintf(
'oci:dbname=//%s:%s/%s',
$config['host'],
$config['port'],
$config['dbname']
);
case 'informix':
return sprintf(
'informix:host=%s; service=%s; database=%s; server=%s; protocol=onsoctcp',
$config['host'],
$config['port'],
$config['dbname'],
$config['server'] ?? 'informixserver'  // server es un parámetro requerido para Informix
);
default:
throw new InvalidArgumentException("Driver no soportado");
}
}
private static function parseOptions($options){
$parsed = [];
foreach($options as $option){
if( strpos($option, '=>') !== false ){
list($key, $value) = explode('=>', $option);
$key = trim($key);
$value = trim($value);
if( defined($key) ){
if( defined($value) ){
$parsed[constant($key)] = constant($value);
}else{
$parsed[constant($key)] = ($value=="true");
}
}
}
}
return $parsed;
}
private static function getMailKeyCode(){
$sql = ["port"=>"", "set"=>[], "options"=>[], "transaction"=>true, "statistics"=>true, "multitenant"=>"", "databaseSYS"=>""];
$dim = explode("|", ePassword(getenv('MAIL_KEYCODE')));
for($n=0; $n<count($dim); $n++){
$command = trim($dim[$n]);
eExplodeOne($command, "=", $key, $value);
$key = trim($key);
$value = trim($value);
if( $value=="true" ){
$value = true;
}else if( $value=="false" ){
$value = false;
}
if( $key[0]=="_" ){
$GLOBALS[$key] = $value;
continue;
}
if( $key=="set" || $key=="options" ){
if( !isset($sql[$key]) || !is_array($sql[$key]) ){
$sql[$key] = array();
}
$sql[$key] = $value;
continue;
}
$sql[$key] = $value;
}
return $sql;
}
public static function id(){
return static::$dbh->lastInsertId();
}
public static function prepare(string $sql, int $cursor=0){
try{
self::tron($sql);
static::$cursor[$cursor] = static::$dbh->prepare($sql);
}catch(PDOException $e){
S::error("", $e->getMessage());
}
}
public static function execute($dimParam, $cursor=0){
static::$cursor[$cursor]->execute( $dimParam );
}
public static function query(string|array $sql, string|array $filter=[], $cursor=0){
$dimParam = [];
list($sql, $dimParam) = self::fillTemplate($sql, $filter);
try{
if( $filter != $dimParam ){
self::tron($sql, $filter);
self::tron($sql, $dimParam);
}else{
self::tron($sql, $filter);
}
static::$cursor[$cursor] = static::$dbh->prepare($sql);
static::$cursor[$cursor]->execute($dimParam);
}catch(PDOException $e){
S::error("", $e->getMessage());
}
}
private static function fillTemplate(string|array $sql, string|array $params){
if( gettype($sql)=="array" ){
list($sql, $addSql) = $sql;
}else{
$addSql = "";
}
if( gettype($params)=="string" ){
if( empty($params) ){
$params = [];
}else{
$data = self::parseSql($params);
$params = $data['params'];
if( strpos($sql, "{{WHERE}}") !== false ){
$sql = str_replace("{{WHERE}}", $data['query'], $sql);
}else{
$sql .= " where {$data['query']}";
}
}
}
$fieldsRemove = array();
$values = [];
$fieldsRemove = [];
$isArrayIndex = self::isArrayIndex($params);
if( !$isArrayIndex ){
$newSql = preg_replace_callback(
'/{{(\w+)}}/',
function($matches) use ($params, &$values, &$fieldsRemove, $sql){
$key = $matches[1];
if( $key==="WHERE" ){
return "{{WHERE}}";
}
if( !array_key_exists($key, $params) ){
S::error("", "Missing parameter {{".$key."}} in {$sql}");
}
$fieldsRemove[] = $key;
$values[] = $params[$key];
return '?';
},
$sql
);
}else{
$newSql = $sql;
}
if( $newSql === null ){
S::error("", "Error processing SQL template: {$sql}");
}
if( !$isArrayIndex && empty($fieldsRemove) ){
list($xWhere, $values) = self::getWhere($params);
if( strpos($newSql, "{{WHERE}}")===false ){
$newSql .= $xWhere;
}else{
$newSql = str_replace("{{WHERE}}", $xWhere, $newSql);
}
}else{
$filter = array();
if( $isArrayIndex ){
$filter = $params;
$values = $params;
}else{
foreach($params as $key=>$value){
if( !in_array($key, $fieldsRemove) ){
$filter[$key] = $value;
}
}
list($xWhere, $addValues) = self::getWhere($filter, array("noWhere"=>true));
if( !empty($xWhere) ){
$newSql .= " AND ".trim($xWhere);
$values = array_merge($values, $addValues);
}
}
}
return array( $newSql.$addSql, $values );
}
private static function isArrayIndex(array $array): bool {
if( $array === [] ) return false;
return array_keys($array) === range(0, count($array) - 1);
}
public static function insertNotExists(string $table, array $data=[], string|array $indexKeys=[]){
if( gettype($indexKeys)=="string" ){
$indexKeys = [$indexKeys];
}
$filter=[];
foreach($indexKeys as $key=>$value){
$filter[$value] = $data[$value];
}
if( self::count($table, $filter) > 0 ){
return;
}
self::insert($table, $data);
}
public static function insert(string $table, array $data=[]){
$keys = array_keys($data);
$values = array_values($data);
if( isset($GLOBALS['_CheckFieldInWhere']) && in_array($table, $GLOBALS['_DimCheckFieldInWhere']) ){
if( !preg_match("/".mb_substr($GLOBALS['_CheckFieldInWhere'],0,-1)."/iu", implode(",", $keys)) ){
S::error("", 'ERROR: Insert "'.$GLOBALS['_CheckFieldInWhere'].'"');
}
}
try{
$sql = "insert into {$table} (". implode(", ", $keys) .") values (". rtrim(str_repeat('?,', count($keys)), ',') .")";
self::tron($sql, $data);
$stmt = static::$dbh->prepare( $sql );
$stmt->execute( $values );
$GLOBALS["_IdRegistro"][trim($table)] = self::id();
}catch(PDOException $e){
S::error("", $e->getMessage());
}
return;
}
public static function update(string $table, array $data=[], array $filter=[]){
list($xWhere, $values) = self::getWhere($filter);
$dimSet = [];
$dimValue = [];
foreach($data as $key=>$value){
if( gettype($value)=="string" && $value[0]==":" ){
$dimSet[] = "{$key}=".substr($value, 1);
}else{
$dimSet[] = "{$key}=?";
$dimValue[] = $value;
}
}
$dimValue = array_merge($dimValue, $values);
try{
$sql = "update {$table} set ". implode(", ", $dimSet) ." {$xWhere}";
self::tron($sql, $dimValue);
$stmt = static::$dbh->prepare( $sql );
$stmt->execute( $dimValue );
}catch(PDOException $e){
S::error("", $e->getMessage());
}
}
public static function delete(string $table, array $filter=[]){
list($xWhere, $values) = self::getWhere($filter);
try{
$sql = "DELETE FROM {$table} {$xWhere}";
self::tron($sql, $filter);
$stmt = static::$dbh->prepare( $sql );
$stmt->execute( $values );
}catch(PDOException $e){
S::error("", $e->getMessage());
}
}
public static function count(string $table, array|string $filter=[]){
if( gettype($filter)=="array" ){
list($xWhere, $values) = self::getWhere($filter);
}else{
$filter = trim($filter);
if( empty($filter) ){
$xWhere = "";
}else if( substr($filter,0,6)=="where " ){
$xWhere = $filter;
}else{
$xWhere = "where {$filter}";
}
$values = [];
}
$sql = "select count(*) from {$table} {$xWhere}";
self::tron($sql);
$stmt = static::$dbh->prepare( $sql );
$stmt->execute( $values );
$row = $stmt->fetch( PDO::FETCH_NUM );
self::tron("count = {$row[0]}");
$GLOBALS["_TReg"] = $row[0];
return $row[0];
}
public static function get($type="assoc", $cursor=0){
if( is_numeric($type) ){
$cursor = (int)$type;
$type = "assoc"; // Valor por defecto para type
}
$isFetchNum = stripos($type, 'num') !== false;
$isFetchAll = stripos($type, 'all') !== false;
$fetchType  = ($isFetchNum) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
$data = $isFetchAll
? static::$cursor[$cursor]->fetchAll($fetchType)
: static::$cursor[$cursor]->fetch($fetchType);
if( $data === false || $data === null || empty($data) ){
return false;
}
return self::convertArrayToUtf8($data);
}
private static function convertArrayToUtf8($data) {
if( is_array($data) ){
return array_map([self::class, 'convertArrayToUtf8'], $data);
}else if( is_string($data) ){
return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
}
return $data;
}
public static function gets($type="assoc", $cursor=0){
if( is_numeric($type) ){
$cursor = (int)$type;
$type = "assoc"; // Valor por defecto para type
}
return self::get($type.",all", $cursor);
}
public static function select(string|array $sql, array $filter = [], int $cursor = 0){        // alias de DB::query()
self::query($sql, $filter, $cursor);
}
public static function selectOne(string|array $sql, array $filter=[], string $type="assoc", int $cursor=0){
if( is_numeric($type) ){
$cursor = (int)$type;
$type = "assoc";
}
self::query($sql, $filter, $cursor);
$row = self::get($type, $cursor);
$nextRow = self::get("num", $cursor);
if( $nextRow !== false ){
S::error("", "More than one record found: ".addslashes($sql));            // throw new RuntimeException("Expected single row but found multiple results", 0, null, $sql);
}
self::free($cursor);
return $row;
}
public static function selectAll(string|array $sql, array $filter=[], string $type="assoc", int $cursor=0){
if( is_numeric($type) ){
$cursor = (int)$type;
$type = "assoc"; // Valor por defecto para type
}
self::query($sql, $filter, $cursor);
$rows = self::gets($type, $cursor);
self::free($cursor);
return $rows;
}
public static function free(int $cursor=0){
if( $cursor >= 0 ){
static::$cursor[$cursor] = null;
}else{          // cuando es negativo
foreach(static::$cursor as $key=>$value){
static::$cursor[$key] = null;
}
}
}
public static function rollBackAll(){
foreach(DB::$listClass as $className){
if( $className::$dbh ){
if( $className::$dbh->inTransaction() ){
self::tron("rollBack in {$className}");
$className::$dbh->rollBack();
}
}
}
}
public static function closeAll(){
foreach(self::$listClass as $className){
if( $className::$dbh ){
if( $className::$dbh->inTransaction() ){
self::tron("commit in {$className}");
$className::$dbh->commit();
}
$className::free(-1);
}
self::tron("close {$className}");
$className::$dbh = null;
}
}
public static function close(){
if( static::$dbh ){
if( static::$dbh->inTransaction() ){
self::tron("commit ".get_called_class());
static::$dbh->commit();
}
self::free(-1);
}
self::tron("close ".get_called_class());
static::$dbh = null;
}
public static function error(){
if( !static::$dbh ){
return;
}
self::tron("rollBack");
static::$dbh->rollBack();
}
public static function isOpen(){
return !empty(self::getHandle());
}
public static function sameDataBase($className){
self::tron(get_called_class()." = SS");
static::$dbh = $className::getHandle();
}
public static function getHandle(){
return static::$dbh;
}
private function noQuotesString($v){
return addslashes($v);
}
public static function getWhere($data, $options=[]){
$dimWhere = array();
$condition = array();
foreach($data as $field => $value){
$value = trim($value);
if( $value === '' || $field[0] == "_" ){
continue;
}
if( isset($options["noFilter"]) && in_array($field, $options["noFilter"]) ){
continue;
}
$alias = "";
if( isset($options["privateAlias"]) && !empty($options["privateAlias"][$field]) ){
$alias = $options["privateAlias"][$field] . ".";
}else if( isset($options["alias"]) ){
$alias = $options["alias"] . ".";
}
$field = "{$alias}{$field}";
if( preg_match('/^\(([^)]+)\)$/', $value, $matches) ){
$values = explode(',', $matches[1]);
$dimWhere = array_merge($dimWhere, $values);
$condition[] = "{$field} IN (" . implode(",", array_fill(0, count($values), "?")) . ")";
}
else if( preg_match('/^\)([^(]+)\($/', $value, $matches) ){
$values = explode(',', $matches[1]);
$dimWhere = array_merge($dimWhere, $values);
$condition[] = "{$field} NOT IN (" . implode(",", array_fill(0, count($values), "?")) . ")";
}
else if( $value === '=' ){
$condition[] = "({$field} IS NULL OR {$field} = ?)";        // "SELECT * FROM tabla WHERE A.sesgo IS NULL OR A.sesgo = ?";
array_push($dimWhere, "");
}
else if( $value[0] === '=' && substr_count($value, "=") > 1 ){
$values = explode("=", $value);
array_shift($values);
$dimWhere = array_merge($dimWhere, $values);
$condition[] = "{$field} IN (" . implode(",", array_fill(0, count($values), "?")) . ")";
}
else if( preg_match('/^(>|<)([^><]+)(>|<)([^><]+)$/', $value, $matches) ){
$operador1 = $matches[1];
$value1 = addslashes(trim($matches[2]));
$operador2 = $matches[3];
$value2 = addslashes(trim($matches[4]));
if( $operador1 === '>' && $operador2 === '<' ){
$condition[] = "({$field} < ? OR {$field} > ?)";
array_push($dimWhere, $value1, $value2);
}else if( $operador1 === '<' && $operador2 === '>' ){
$condition[] = "({$field} < ? OR {$field} > ?)";
array_push($dimWhere, $value1, $value2);
}else{
$value = addslashes($value);
array_push($dimWhere, $value);
$condition[] = "{$field} = ?";
}
}
else if( preg_match('/^(>=|<=|>|<)(.+)$/', $value, $matches) ){
$operador = $matches[1];
array_push($dimWhere, trim($matches[2]));
$condition[] = "{$field} {$operador} ?";
}
else if( strpos($value, '*') !== false || strpos($value, '?') !== false ){
$valueLike = str_replace('*', '%', str_replace('?', '_', addslashes($value)));
array_push($dimWhere, $valueLike);
$condition[] = "{$field} LIKE ?";
}
else{
array_push($dimWhere, $value);
$condition[] = "{$field}=?";
}
}
$where = ( isset($options["noWhere"]) && $options["noWhere"]==true ) ? "" : "WHERE";
return array(empty($condition) ? '' : " {$where} ". implode(' AND ', $condition), $dimWhere);
}
public static function getCondition($data, $labels, $options=array()){
foreach($data as $field => $value){
if( substr($field,0,7)=="_INPUT_" && isset($data[substr($field,7)]) ){
$data[substr($field,7)] = $data[$field];
}
}
$condition = array();
$AND = "y";
$OR = "o";
$LIKE = "contiene"; // sin uso
$IN = "(en)";
$NOT_IN = "(no en)";
$IS_NULL = "(vacío)";
foreach($data as $field => $value){
$value = trim($value);
$label = $labels[$field].":";
if( $value==='' || $field[0]=="_" ){
continue;
}
if( isset($options["noFilter"]) && in_array($field, $options["noFilter"]) ){
continue;
}
if( preg_match('/^\(([^)]+)\)$/', $value, $matches) ){
$values = explode(',', $matches[1]);
$valuesLimpios = array_map('noQuotesString', $values);
$condition[] = "{$label} {$IN} " . implode(', ', $valuesLimpios);
}
elseif( preg_match('/^\)([^(]+)\($/', $value, $matches) ){
$values = explode(',', $matches[1]);
$valuesLimpios = array_map('noQuotesString', $values);
$condition[] = "{$label} {$NOT_IN} " . implode(', ', $valuesLimpios);
}
elseif( $value === '=' ){
$condition[] = "{$label} {$IS_NULL}";
}
elseif( $value[0] === '=' && substr_count($value, "=")>1 ){
$values = explode("=", $value);
array_shift($values);
$valuesLimpios = array_map('noQuotesString', $values);
$condition[] = "{$label} {$IN} " . implode(', ', $valuesLimpios);
}
elseif( preg_match('/^(>|<)([^><]+)(>|<)([^><]+)$/', $value, $matches) ){
$operador1 = $matches[1];
$value1 = addslashes(trim($matches[2]));
$operador2 = $matches[3];
$value2 = addslashes(trim($matches[4]));
if( $operador1 === '>' && $operador2 === '<' ){
$condition[] = "{$label} > {$value1} {$AND} < {$value2}";
}else if( $operador1 === '<' && $operador2 === '>') {
$condition[] = "{$label} < {$value1} {$OR} > {$value2}";
}else{
$value = addslashes($value);
$condition[] = "{$label} '{$value}'";
}
}
else if( preg_match('/^(>=|<=|>|<)(.+)$/', $value, $matches) ){
$operador = $matches[1];
$valueComparacion = addslashes(trim($matches[2]));
$condition[] = "{$label} {$operador} {$valueComparacion}";
}
else if( strpos($value, '*') !== false || strpos($value, '?') !== false ){
$valueLike =  addslashes($value); // str_replace('*', '%', str_replace('?', '_', addslashes($value)));
$condition[] = "{$label} {$valueLike}"; // {$LIKE}
}
else{
$value = addslashes($value);
$condition[] = "{$label} {$value}";
}
}
return $condition;
}
public static function tron($str, $data=[]){
global $_DEBUG;
if( empty($_DEBUG) && strpos($_ENV["APP_TRON"], "Q")===false ){
return;
}
if( gettype($data)=="string" ){
$data = [$data];
}
$cadena = '';
foreach($data as $clave=>$valor){
$cadena .= "\n\t\t{$clave}=\"{$valor}\", ";
}
$cadena = rtrim($cadena, ', ');
$driver = ((static::$dbh==null) ? "" : get_called_class().": ");
if( $_DEBUG==4 ){
eLogDebug("{$driver}{$str}{$cadena}");
}else if( $_DEBUG==1 ){
eTrace("Log:[{$driver}{$str}{$cadena}]");
}else{
eTron("\t{$driver}{$str}{$cadena}");
}
}
public static function activeTransaction(){
return static::$dbh->inTransaction();
}
public static function beginTransaction(){
return static::$dbh->beginTransaction();
}
public static function commit(){
return static::$dbh->commit();
}
public static function rollBack(){
return static::$dbh->rollBack();
}
public static function insertArray(string $table, array $fields, array $data=[]){
$check = function($table, $fields, $data){
if( !is_array($data) || !is_array($data[0] ?? null) ){
S::error("", "Datos deben ser array bidimensional");
}
$total = count($data);
if( $total==0 ){
S::error("", "Inserción masiva, no hay registros a insertar");
}
$totalFields = count($fields);
$totalValues = count($data[0]);
if( $totalFields!=$totalValues ){
S::error("", "FIELD/VALUE MISMATCH (Table: {$table}). Expected {$totalValues} fields, got {$totalFields}.");
}
return $total;
};
$total	= $check($table, $fields, $data);
$values = [];
$totalRegistros = 0;
$sql = "insert into {$table} (". implode(", ", $fields) .") values ";
$templateValues = "(". rtrim(str_repeat('?,', count($fields)), ',') .")";
$iniMemory = memory_get_usage();
for($i=0; $i<$total; $i++){
$totalRegistros++;
$values = array_merge($values, $data[$i]);
if( (memory_get_usage() - $iniMemory) > 30000 ){
try{
$stmt = static::$dbh->prepare( $sql . rtrim(str_repeat("{$templateValues},", $totalRegistros), ",") );
$stmt->execute( $values );
}catch(PDOException $e){
S::error("", "Error in transaction: ". $e->getMessage());
}
$values = [];
$totalRegistros = 0;
$iniMemory = memory_get_usage();
}
}
if( $totalRegistros > 0  ){
try{
$stmt = static::$dbh->prepare( $sql . rtrim(str_repeat("{$templateValues},", $totalRegistros), ",") );
$stmt->execute( $values );
}catch(PDOException $e){
S::error("Error in transaction", "Error in transaction: ". $e->getMessage());
}
}
}
public static function extends($nameClass, $setup=[]){
$codeClass = "class {$nameClass} extends DB {
protected static \$dbh = null;
protected static \$dsn = null;
protected static \$driver = null;
protected static \$cursor = [];
}";
eval($codeClass);
if( !empty($setup) ){
$nameClass::open($setup);
}
}
public static function runDailyProcess($function=null){
if( $function==null || !function_exists($function) ){
return;
}
$today = date('Y-m-d');
try{
if( SS::count("gs_system")==0 ){
$execute = true;
SS::insert("gs_system", ["last_execution"=>$today]);
}else{
$execute = false;
$data = self::selectOne("SELECT last_execution FROM gs_system");
if( $data['last_execution'] >= $today ){
return;
}
}
if( !self::activeTransaction() ){
self::beginTransaction();
}
$data = self::selectOne("SELECT last_execution FROM gs_system FOR UPDATE");
if( !$execute && $data && $data['last_execution'] >= $today ){
return;
}
$function($today);      // llamamos a la función pasada como parámetro una sola vez al día
if( $data ){
self::update("gs_system", [ "last_execution" => $today ]);
self::commit();
self::beginTransaction();
}
}catch( PDOException $e ){
S::error("", $e->getMessage());
}
return;
}
public static function tableExists($table, $schema = null) {
if( $schema==null && strpos($table, ".")===true ){
list($schema, $table) = explode(".", $table);
}
$fullTable = $schema ? "{$schema}.{$table}" : $table;
if( isset(static::$tableExists[$fullTable]) ){
return static::$tableExists[$fullTable];
}
$errorMode = static::$dbh->getAttribute(PDO::ATTR_ERRMODE);
try {
static::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$stmt = self::query("SELECT 1 FROM {$fullTable} WHERE 1=0", [], 10);
static::$dbh->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
$result =  $stmt !== false;
static::$tableExists[$fullTable] = $result;
return $result;
} catch(Throwable $e){
static::$dbh->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
static::$tableExists[$fullTable] = false;
return false;
}
}
public static function columnCount(int $cursor=0){
return static::$cursor[$cursor]->columnCount();
}
public static function getDriver(){
return static::$driver;
}
public static function isDriver($driver=null){
if( $driver==null ){
return static::$driver;
}
$dim = explode(",", S::nsp($driver));
for($i=0; $i<count($dim); $i++){
if( static::$driver==$dim[$i] ){
return true;
}
}
return false;
}
public static function normalizeColumnType($nativeType){
$typeMaps = [
'mysql' => [
'TINY' => self::TYPE_INTEGER,
'SHORT' => self::TYPE_INTEGER,
'LONG' => self::TYPE_INTEGER,
'LONGLONG' => self::TYPE_INTEGER,
'INT24' => self::TYPE_INTEGER,
'FLOAT' => self::TYPE_FLOAT,
'DOUBLE' => self::TYPE_FLOAT,
'DECIMAL' => self::TYPE_FLOAT,
'STRING' => self::TYPE_STRING,
'VAR_STRING' => self::TYPE_STRING,
'BLOB' => self::TYPE_BINARY,
'DATE' => self::TYPE_DATE,
'TIME' => self::TYPE_TIME,
'DATETIME' => self::TYPE_DATETIME,
'TIMESTAMP' => self::TYPE_DATETIME,
'YEAR' => self::TYPE_INTEGER,
'GEOMETRY' => self::TYPE_BINARY,
'TINY(1)' => self::TYPE_BOOLEAN,
'NEWDECIMAL' => self::TYPE_INTEGER
],
'pgsql' => [
'int2' => self::TYPE_INTEGER,
'int4' => self::TYPE_INTEGER,
'int8' => self::TYPE_INTEGER,
'numeric' => self::TYPE_FLOAT,
'float4' => self::TYPE_FLOAT,
'float8' => self::TYPE_FLOAT,
'varchar' => self::TYPE_STRING,
'text' => self::TYPE_STRING,
'bpchar' => self::TYPE_STRING,
'bool' => self::TYPE_BOOLEAN,
'date' => self::TYPE_DATE,
'time' => self::TYPE_TIME,
'timestamp' => self::TYPE_DATETIME,
'timestamptz' => self::TYPE_DATETIME,
'bytea' => self::TYPE_BINARY,
'json' => self::TYPE_JSON,
'jsonb' => self::TYPE_JSON,
'uuid' => self::TYPE_STRING
],
'informix' => [
'SMALLINT' => self::TYPE_INTEGER,
'INTEGER' => self::TYPE_INTEGER,
'INT8' => self::TYPE_INTEGER,
'SERIAL' => self::TYPE_INTEGER,
'SERIAL8' => self::TYPE_INTEGER,
'FLOAT' => self::TYPE_FLOAT,
'SMALLFLOAT' => self::TYPE_FLOAT,
'DECIMAL' => self::TYPE_FLOAT,
'MONEY' => self::TYPE_FLOAT,
'CHAR' => self::TYPE_STRING,
'VARCHAR' => self::TYPE_STRING,
'LVARCHAR' => self::TYPE_STRING,
'BOOLEAN' => self::TYPE_BOOLEAN,
'DATE' => self::TYPE_DATE,
'DATETIME' => self::TYPE_DATETIME,
'INTERVAL' => self::TYPE_STRING,
'TEXT' => self::TYPE_STRING,
'BYTE' => self::TYPE_BINARY,
'BLOB' => self::TYPE_BINARY,
'CLOB' => self::TYPE_STRING
],
'oci' => [
'NUMBER' => self::TYPE_FLOAT,
'FLOAT' => self::TYPE_FLOAT,
'CHAR' => self::TYPE_STRING,
'VARCHAR2' => self::TYPE_STRING,
'NCHAR' => self::TYPE_STRING,
'NVARCHAR2' => self::TYPE_STRING,
'DATE' => self::TYPE_DATETIME,
'TIMESTAMP' => self::TYPE_DATETIME,
'TIMESTAMP WITH TIME ZONE' => self::TYPE_DATETIME,
'TIMESTAMP WITH LOCAL TIME ZONE' => self::TYPE_DATETIME,
'INTERVAL YEAR TO MONTH' => self::TYPE_STRING,
'INTERVAL DAY TO SECOND' => self::TYPE_STRING,
'RAW' => self::TYPE_BINARY,
'LONG RAW' => self::TYPE_BINARY,
'BLOB' => self::TYPE_BINARY,
'CLOB' => self::TYPE_STRING,
'NCLOB' => self::TYPE_STRING,
'BFILE' => self::TYPE_BINARY,
'ROWID' => self::TYPE_STRING,
'UROWID' => self::TYPE_STRING,
'NUMBER(1)' => self::TYPE_BOOLEAN
]
];
$driver = self::getDriver();
if( !isset($typeMaps[$driver]) ){
S::error("", "Driver [{$driver}] not found in [normalizeColumnType()]");
}
if( isset($typeMaps[$driver][$nativeType]) ){
return $typeMaps[$driver][$nativeType];
}
foreach( $typeMaps[$driver] as $typePattern => $normalizedType ){
if( strpos($nativeType, $typePattern) === 0 ){
return $normalizedType;
}
}
S::error("", "El tipo de dato [{$nativeType}] no está definido para el driver [{$driver}]");
}
public static function structureSQL($cursor=0){
$totalFields = static::$cursor[$cursor]->columnCount();
$setup = [];
$dimTables = [];
$hayAlias = false;
for($i=0; $i < $totalFields; $i++){
$meta = static::$cursor[$cursor]->getColumnMeta($i);
$meta['native_type'] = self::normalizeColumnType($meta['native_type']);
$meta['type']	= $meta['native_type'];
$meta['leng']	= $meta['len'];
$meta['null']	= "";
$meta['default']= "";
$meta['primary']= "";
$meta['serial']	= "";
$meta['extra']	= "";
$setup[$meta['name']] = $meta;
if( !self::tableExists($meta["table"]) ){
$hayAlias = true;
}
}
if( $hayAlias ){
return $setup;
}
for($i=0; $i < $totalFields; $i++){
$meta = static::$cursor[$cursor]->getColumnMeta($i);
$meta['native_type'] = self::normalizeColumnType($meta['native_type']);
if( !isset($dimTables[$meta["table"]]) ){
$dimTables[$meta["table"]] = DBTable::structure($meta["table"]);
}
for($n=0; $n<count($dimTables[$meta["table"]]); $n++){
if( $dimTables[$meta["table"]][$n]["name"]==$meta["name"] ){
$index = $n;
break;
}
}
$setup[$meta['name']] = array_merge($setup[$meta['name']], $dimTables[$meta["table"]][$index]);
}
return $setup;
}
public static function parseSql($sql){
$params = array();
$query = '';
$addSql = "";
$pattern = '/ORDER\s*BY/i';
$resultado = preg_split($pattern, $sql);
if( count($resultado) > 1 ){
$sql = $resultado[0];
$addSql = " ORDER BY ".$resultado[1];
}
$length = strlen($sql);
$i = 0;
while($i < $length){
$char = $sql[$i];
if( $char === "'" || $char === '"' ){
$quote = $char;
$string = '';
$i++;
while($i < $length){
$char = $sql[$i];
if( $char === $quote ){
if( $i + 1 < $length && $sql[$i + 1] === $quote ){         // Verificamos si es una comilla escapada
$string .= $quote;
$i += 2;
continue;
}else{	                // comilla de cierre
break;
}
}else if( $char === '\\' && $i + 1 < $length ){
$string .= $char . $sql[$i + 1];
$i += 2;
continue;
}else{
$string .= $char;
}
$i++;
}
$params[] = $string;
$query .= '?';
}else if( preg_match('/\d/', $char) ){
$number = '';
if( $i > 0 && $sql[$i - 1] === '-' ){
$prevChar = $i > 1 ? $sql[$i - 2] : ' ';
if( preg_match('/[\s\(=<>!,]/', $prevChar) ){
$query = substr($query, 0, -1); // Removemos el '-' del query
$number = '-';
}
}
while( $i < $length && (preg_match('/[\d\.]/', $sql[$i])) ){
$number .= $sql[$i];
$i++;
}
$i--; // Retrocedemos uno porque el bucle principal incrementará
$prevContext = $i > 0 ? $sql[$i - strlen($number)] : ' ';
$nextContext = $i + 1 < $length ? $sql[$i + 1] : ' ';
if( preg_match('/[\s\(=<>!,]/', $prevContext) &&
preg_match('/[\s\),;=<>!]/', $nextContext) ){
if( strpos($number, '.') !== false ){
$params[] = floatval($number);
}else{
$params[] = intval($number);
}
$query .= '?';
}else{
$query .= $number;
}
}else{
$query .= $char;
}
$i++;
}
$query .= $addSql;
return array(
'query' => $query,
'params' => $params
);
}
public static function parseSqlExe($sql, $where, int $cursor=0){
$where = (empty($where) ? "" : " where {$where}");
if( strpos($sql, "{{WHERE}}") !== false ){
$sql = str_replace("{{WHERE}}", $where, $sql);
$where = "";
}
$tmp = DB::parseSql("{$sql}{$where}");
DB::query($tmp["query"], $tmp["params"], $cursor);
}
public static function parseOrderBy($Ordenacion=""){
if( empty($Ordenacion) ){
return "";
}
if( eSubstrCount ( $Ordenacion, ' group by ' ) > 0 ){
$tmp = explode(' group by ', $Ordenacion );
$Ordenacion = ' group by '.$tmp[1]. ' order by '.$tmp[0];
}else{
$Ordenacion = 'order by '.$Ordenacion;
}
return $Ordenacion;
}
public static function queryLog($sql, $xTabla, $xClave='', $xUser=""){
if( empty($xUser) ){
$xUser = $_SESSION["_User"];
}
$sql = trim($sql);
if( preg_match("/^UPDATE/iu", $sql) ){
$xOp = 'M';
}else if( preg_match("/^DELETE/iu", $sql) ){
$xOp = 'B';
}else if( preg_match("/^INSERT/iu", $sql) ){
$xOp = 'A';
}else{
$xOp = 'C';
}
self::insert("{$_ENV['SYSDB']}gs_log", [
"cdi"		=> date('Y-m-d H:i:s'),
"operacion"	=> $xOp,
"cd_gs_user"=> $xUser,
"tabla"		=> $xTabla,
"clave"		=> $xClave,
"sqlexe"	=> $sql
]);
self::query($sql);
}
public static function unobfuscate($pass) {
if( !self::isObfuscated($pass) ){
return $pass;
}
$igual = 4-(mb_strlen($pass)-8)%4;
if( $igual>3 ) $igual = 0;
$xPass = $pass . str_repeat("=", $igual);
$crc = str_pad(dechex(crc32(mb_substr($xPass, 8))), 8, "0", STR_PAD_LEFT);
if( mb_substr($xPass,0,8)==$crc ){
$pass = mb_substr(gzuncompress(base64_decode(mb_substr($xPass, 8))), 5);
}
return $pass;
}
public static function obfuscate($pass) {
if (self::isObfuscated($pass)) {
return $pass;
}
$dim = [47,57,65,90,97,122];
$txt = mb_chr(43);
for($i=0; $i<6; $i+=2){
for($p=$dim[$i]; $p<=$dim[$i+1]; $p++){
$txt .= mb_chr($p);
}
}
$add = "";
for($i=0; $i<5; $i++) $add .= $txt[rand(0,mb_strlen($txt)-1)];
$xPass = base64_encode(gzcompress($add.$pass));
$crc = str_pad(dechex(crc32($xPass)), 8, "0", STR_PAD_LEFT);
return str_replace("=", "", $crc.$xPass);
}
private static function isObfuscated($pass) {
if (mb_strlen($pass) <= 25 || !preg_match('/^[0-9a-f]{8}/i', $pass)) {
return false;
}
$paddingLength = (4 - (mb_strlen($pass) - 8) % 4) % 4;
$xPass = $pass . str_repeat("=", $paddingLength);
$storedChecksum = mb_substr($xPass, 0, 8);
$data = mb_substr($xPass, 8);
$calculatedChecksum = str_pad(dechex(crc32($data)), 8, "0", STR_PAD_LEFT);
return $storedChecksum===$calculatedChecksum;
}
public static function extractFields( $sql ){
if( $GLOBALS['_DEBUGSQL'] ) eLogDebug(__FUNCTION__);
global $_DEBUG;
if( $_DEBUG==1 ) eTrace( "DimFields[]" );
$DimCampo = array();
$sql = trim($sql);
if( mb_strtoupper(mb_substr($sql,0,7)) == 'SELECT ' ) $sql = mb_substr($sql,7);
if( eSubstrCount( mb_strtoupper($sql), ' FROM ' ) > 0 ){
$sql = mb_substr($sql, 0, mb_strpos(mb_strtoupper($sql), ' FROM '));
}
$Expresion = false;
$NomCampo = '';
for( $c=0; $c<mb_strlen($sql); $c++ ){
$ca = mb_substr($sql,$c,1);
switch( $ca ){
case '[':
case '(':
$Expresion = true;
$NomCampo .= $ca;
break;
case ']':
case ')':
$Expresion = false;
$NomCampo .= $ca;
break;
case ',':
if( $Expresion ){
$NomCampo .= $ca;
}else{
$DimCampo[] = $NomCampo;
$NomCampo = '';
}
break;
default:
$NomCampo .= $ca;
}
}
$DimCampo[] = $NomCampo;
for( $c=0; $c<count($DimCampo); $c++ ){
if( eSubstrCount($DimCampo[$c],' as ') > 0 ){
list( , $txt ) = explode(' as ',$DimCampo[$c]);
$DimCampo[$c] = trim($txt);
}else if( eSubstrCount($DimCampo[$c],'(') > 0 ){
list( $comando, $txt ) = explode('(',$DimCampo[$c]);
if( $comando!='COUNT' ){
list( $txt ) = explode(',',$txt);
$DimCampo[$c] = trim($txt);
}
}
}
return $DimCampo;
}
public static function getHashRecord($xTabla, $NTX, $DBRLOCKNOFIELD=""){
if( !is_array($xTabla) ) $xTabla = [$xTabla];
if( trim($NTX)!='' ) $NTX = ' where '.$NTX;
$sRLOCK = $_SESSION["_Connection_"];
if( !empty($DBRLOCKNOFIELD) && $DBRLOCKNOFIELD[0]!="," ){
$DBRLOCKNOFIELD = ",".preg_replace('/\s+/', '', $DBRLOCKNOFIELD).",";
}
for($n=0; $n<count($xTabla); $n++){
$sTabla = '';
if( eSubstrCount($xTabla[$n], ", {$_ENV['SYSDB']}gs_dct zz ") > 0 ){
list($sTabla) = explode(", {$_ENV['SYSDB']}gs_dct zz ", $xTabla[$n]);
$sTabla = trim($sTabla).'.';
}
DB::query("select {$sTabla}* from ".$xTabla[$n].$NTX, [], 1);
$r = DB::get(1);
if( is_array($r) ){
foreach($r as $key=>$val){
if( !is_numeric($key) && eSubstrCount($DBRLOCKNOFIELD, ",{$key},")==0 ){
if( !is_object($val) ){
$sRLOCK .= $key.$val;
}else{
$sRLOCK .= $key.$val->load();
}
}
}
}
}
return md5($sRLOCK);
}
}
class SS extends DB {
protected static $dbh = null;
protected static $dsn = null;
protected static $driver = null;
protected static $cursor = [];
}