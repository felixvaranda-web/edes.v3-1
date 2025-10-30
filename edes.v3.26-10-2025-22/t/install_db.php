<?php
function checkingInstallationDB($db){
global $CreateDatabase;
$drive	= $db["drive"];
$host	= $db["host"];
$port	= $db["port"];
$dbname = $db["dbname"];
$user	= $db["user"];
$pass	= $db["pass"];
$suid	= $db['suid'];
$tab   = "    ";
$enter = ( PHP_SAPI === "cli" ) ? "\n" : "<br>";
echo "{$enter}{$tab}1/14. Intentando conectar al servidor \"{$drive}\"...{$enter}";
$result = DBI::checkService($host, $port, $drive);
if ($result) {
echo "{$tab}{$tab}✓ Conexión al servidor \"{$drive}\" exitosa{$enter}";
} else {
die("{$tab}{$tab}✗ Fallo al conectar");
}
echo "{$enter}{$tab}2/14. Abriendo conexión...{$enter}";
$dbConfig = [
'driver'	=> $drive,
'host'		=> $host,
'database'	=> '',
'username'	=> $user,
'password'	=> $pass,
'port'		=> $port
];
DBI::open($dbConfig);
echo "{$enter}{$tab}2/14. Comprobando la existencia de la DDBB '{$dbname}'...{$enter}";
try {
if (DBI::checkDatabaseExists($dbname, $drive)) {
$query = DBI::getQueryDrop($dbname, $drive);
DBI::query($query);
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al comprobar si existe la DDBB: " . $e->getMessage());
}
if( $CreateDatabase ){
echo "{$enter}{$tab}3/14. Intentando crear DDBB '{$dbname}'...{$enter}";
try {
DBI::createDatabaseStructure($dbname, $suid, $drive, [
'username' => $user,
'password' => $pass
]);
echo "{$tab}{$tab}✓ DDBB \"{$dbname}\" creada correctamente{$enter}";
if( !empty($suid) ){
echo "{$tab}{$tab}✓ SCHEMA \"{$suid}\" creada correctamente{$enter}";
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al crear DDBB: " . $e->getMessage());
}
}
echo "{$enter}{$tab}3/14. Reconectar a la nueva base de datos '{$dbname}'...{$enter}";
$dbConfig['database'] = $dbname;
DBI::open($dbConfig);
echo "{$enter}{$tab}5/14. Configurando charset UTF-8...{$enter}";
DBI::setCharset('utf8mb4');
echo "{$tab}{$tab}✓ Charset establecido: " . DBI::getCurrentCharset() . "{$enter}";
echo "{$enter}{$tab}6/14. Verificando si existe la tabla 'prueba'...{$enter}";
try {
if (DBI::tableExists('prueba')) {
echo "{$tab}{$tab}✗ La tabla ya existe{$enter}";
if (PHP_SAPI === "cli") {
echo "{$tab}{$tab}¿Borrar la tabla 'prueba'? (y/n): ";
$respuesta = (defined('STDIN') ? trim(fgets(STDIN)) : '');
if ($respuesta === 'y') {
DBI::dropTable('prueba');
echo "{$tab}{$tab}✓ Tabla borrada{$enter}";
} else {
die("{$tab}{$tab}✗ La tabla ya existe{$enter}");
}
} else {
DBI::dropTable('prueba');
echo "{$tab}{$tab}✓ Tabla existente borrada{$enter}";
}
} else {
echo "{$tab}{$tab}✓ La tabla no existe, se puede crear{$enter}";
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al verificar si existe la tabla: " . $e->getMessage());
}
echo "{$enter}{$tab}7/14. Intentando crear tabla `prueba` con codificación UTF-8...{$enter}";
try {
if (DBI::createTestTable('prueba')) {
echo "{$tab}{$tab}✓ Tabla creada correctamente{$enter}";
$collation = DBI::getTableCollation('prueba');
if ($collation && $collation !== 'N/A') {
echo "{$tab}{$tab}{$tab}Collation de la tabla: {$collation}{$enter}";
}
} else {
throw new Exception("{$tab}{$tab}✗ No se pudo crear la tabla");
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al crear tabla: " . $e->getMessage());
}
echo "{$enter}{$tab}8/14. Intentando insertar datos de prueba (2)...{$enter}";
try {
if (DBI::insertTestData('prueba')) {
echo "{$tab}{$tab}✓ Datos de prueba insertados correctamente{$enter}";
} else {
throw new Exception("{$tab}{$tab}✗ No se pudieron insertar los datos");
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al insertar datos: " . $e->getMessage());
}
echo "{$enter}{$tab}9/14. Verificando datos insertados...{$enter}";
try {
$total = DBI::count('prueba');
echo "{$tab}{$tab}✓ Registros encontrados: {$total}{$enter}";
if ($total > 0) {
DBI::query("SELECT * FROM prueba");
$datos = DBI::get();
foreach($datos as $key=>$value){
foreach($value as $key2=>$value2){
if( $key2=="nombre" ){
echo "{$tab}{$tab}{$tab}- {$key2} = {$value2}{$enter}";
}
}
}
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al verificar datos: " . $e->getMessage());
}
echo "{$enter}{$tab}10/14. Borrando tabla...{$enter}";
try {
if (DBI::dropTable('prueba')) {
echo "{$tab}{$tab}✓ Tabla borrada correctamente{$enter}";
} else {
throw new Exception("{$tab}{$tab}✗ No se pudo borrar la tabla");
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al borrar tabla: " . $e->getMessage());
}
echo "{$enter}{$tab}11/14. Verificando que la tabla 'prueba' se ha borrado...{$enter}";
try {
if (!DBI::tableExists('prueba')) {
echo "{$tab}{$tab}✓ Tabla eliminada correctamente{$enter}";
} else {
echo "{$tab}{$tab}✗ La tabla no se ha borrado{$enter}";
DBI::dropTable('prueba');
}
} catch (Exception $e) {
echo "{$tab}{$tab}⚠ Error al verificar borrado de tabla: " . $e->getMessage() . "{$enter}";
}
if( $CreateDatabase ){
echo "{$enter}{$tab}12/14. Borrando DDBB...{$enter}";
try {
$query = DBI::getQueryDrop($dbname, $drive);
if (DBI::query($query)) {
echo "{$tab}{$tab}✓ DDBB borrada correctamente{$enter}";
} else {
throw new Exception("{$tab}{$tab}✗ No se pudo borrar la base de datos");
}
} catch (Exception $e) {
die("{$tab}{$tab}✗ Error al borrar la DDBB: " . $e->getMessage());
}
echo "{$enter}{$tab}13/14. Verificando la existencia de la DDBB '{$dbname}'...{$enter}";
try {
sleep(1);
if (!DBI::checkDatabaseExists($dbname, $drive)) {
echo "{$tab}{$tab}✓ DDBB eliminada correctamente{$enter}";
} else {
echo "{$tab}{$tab}✗ La DDBB no se ha podido borrar{$enter}";
$query = DBI::getQueryDrop($dbname, $drive);
DBI::query($query);
}
} catch (Exception $e) {
echo "{$tab}{$tab}⚠ Error al verificar borrado de DDBB: " . $e->getMessage() . "{$enter}";
}
}
echo "{$enter}{$tab}14/14. Cerrando conexiones...{$enter}";
try {
DBI::closeAll();
echo "{$tab}{$tab}✓ Conexiones cerradas{$enter}";
echo "{$enter}{$tab}Test de DDBB completado con éxito{$enter}{$enter}";
} catch (Exception $e) {
echo "{$tab}{$tab}⚠ Error al cerrar conexiones: " . $e->getMessage() . "{$enter}";
}
}