<?PHP
class DBTable {
public static function structure($tableName, $schema = null): array {
$driver = SS::getDriver();
switch( $driver ){
case 'mysql':
return self::getMySQLStructure($tableName);
case 'pgsql':
return self::getPostgreSQLStructure($tableName, $schema);
case 'informix':
return self::getInformixStructure($tableName, $schema);
case 'oci':
return self::getOracleStructure($tableName, $schema);
default:
throw new Exception("Driver no soportado: {$driver}");
}
}
private static function getMySQLStructure($tableName){
SS::query("DESCRIBE `{$tableName}`", [], 9);
$columns = [];
while( $row = SS::get(9) ){
$columns[] = [
'name'		=> $row['Field'],
'type'		=> self::parseDataType($row['Type']),
'leng'		=> self::parseLength($row['Type']),
'null'		=> $row['Null'] === 'YES',
'default'	=> $row['Default'],
'primary'	=> $row['Key'] === 'PRI',
'serial'	=> strpos($row['Extra'], 'auto_increment') !== false,
'extra'		=> $row['Extra']
];
}
return $columns;
}
public static function viewStructureTables($sExeSQL){
$sExeSQL = str_replace(' ', '', $sExeSQL);
$TmpSql = explode(',', $sExeSQL);
for($i=0; $i<count($TmpSql); $i++){
if( mb_substr($TmpSql[$i],-1)==';' ){
$TmpSql[$i] = trim(mb_substr($TmpSql[$i],0,-1));
}
if( $i>0 ) echo '<br><br>';
$txt = '';
$dim = DBTable::structure($TmpSql[$i]);
echo "<table>";
echo "<tr><th colspan=8 class='alignCenter'>Table {$TmpSql[$i]}</th></tr>";
echo "<tr>";
echo "<th>name</th>";
echo "<th>type</th>";
echo "<th>leng</th>";
echo "<th>null</th>";
echo "<th>default</th>";
echo "<th>primary</th>";
echo "<th>serial</th>";
echo "<th>extra</th>";
echo "</tr>";
for($p=0; $p<count($dim); $p++){
echo "<tr>";
foreach($dim[$p] as $k=>$v){
echo "<td>{$v}</td>";
}
echo "</tr>";
}
$dimIndex = DBTable::indexes($TmpSql[$i]);
for($p=0; $p<count($dimIndex); $p++){
echo "<tr>";
echo "<td>{$dimIndex[$p]['name']}</td>";
echo "<td>";
if( $dimIndex[$p]['primary'] ){
echo "primary";
}else if( $dimIndex[$p]['unique'] ){
echo "unique";
}
echo "</td>";
echo "<td colspan=6>";
$tmp2 = [];
for($j=0; $j<count($dimIndex[$p]["columns"]); $j++){
array_push($tmp2, $dimIndex[$p]["columns"][$j]["name"]);
}
echo implode(", ", $tmp2);
echo "</td>";
echo "</tr>";
}
echo "</table>";
}
}
private static function getPostgreSQLStructure($tableName, $schema = 'public'){
$sql = "
SELECT
c.column_name,
c.data_type,
c.character_maximum_length,
c.is_nullable,
c.column_default,
CASE WHEN tc.constraint_type = 'PRIMARY KEY' THEN true ELSE false END as is_primary_key,
CASE WHEN c.column_default LIKE 'nextval%' THEN true ELSE false END as is_auto_increment
FROM information_schema.columns c
LEFT JOIN information_schema.key_column_usage kcu ON c.table_name = kcu.table_name
AND c.column_name = kcu.column_name
AND c.table_schema = kcu.table_schema
LEFT JOIN information_schema.table_constraints tc ON kcu.constraint_name = tc.constraint_name
AND kcu.table_schema = tc.table_schema
WHERE c.table_name = :table_name
AND c.table_schema = :schema
ORDER BY c.ordinal_position
";
SS::query($sql, ['table_name' => $tableName, 'schema' => $schema], 9);
$columns = [];
while( $row = SS::get(9) ){
$columns[] = [
'name'		=> $row['column_name'],
'type'		=> $row['data_type'],
'leng'		=> $row['character_maximum_length'],
'null'		=> $row['is_nullable'] === 'YES',
'default'	=> $row['column_default'],
'primary'	=> $row['is_primary_key'],
'serial'	=> $row['is_auto_increment'],
'extra'		=> null
];
}
return $columns;
}
private static function getInformixStructure($tableName, $schema = null){
$whereSchema = $schema ? "AND t.owner = '{$schema}'" : "";
$sql = "
SELECT
c.colname as column_name,
c.coltype,
c.collength,
CASE WHEN c.coltype >= 256 THEN 'YES' ELSE 'NO' END as is_nullable,
c.defval as column_default
FROM syscolumns c
JOIN systables t ON c.tabid = t.tabid
WHERE t.tabname = '{$tableName}' {$whereSchema}
ORDER BY c.colno
";
SS::query($sql, [], 9);
$columns = [];
while( $row = SS::get(9) ){
$columns[] = [
'name'		=> trim($row['column_name']),
'type'		=> self::parseInformixDataType($row['coltype']),
'leng'		=> $row['collength'],
'null'		=> $row['is_nullable'] === 'YES',
'default'	=> $row['column_default'],
'primary'	=> false, // Requiere consulta adicional
'serial' 	=> false, // Informix usa SERIAL
'extra'		=> null
];
}
return $columns;
}
private static function getOracleStructure($tableName, $schema = null){
$whereSchema = $schema ? "AND c.owner = '{$schema}'" : "AND c.owner = USER";
$sql = "
SELECT
c.column_name,
c.data_type,
c.data_length,
c.nullable,
c.data_default,
CASE WHEN cc.constraint_type = 'P' THEN 'Y' ELSE 'N' END as is_primary_key
FROM all_tab_columns c
LEFT JOIN all_cons_columns ccc ON c.table_name = ccc.table_name
AND c.column_name = ccc.column_name
AND c.owner = ccc.owner
LEFT JOIN all_constraints cc ON ccc.constraint_name = cc.constraint_name
AND ccc.owner = cc.owner
AND cc.constraint_type = 'P'
WHERE c.table_name = '{$tableName}' {$whereSchema}
ORDER BY c.column_id
";
SS::query($sql, [], 9);
$columns = [];
while( $row = SS::get(9) ){
$columns[] = [
'name'		=> $row['COLUMN_NAME'],
'type'		=> $row['DATA_TYPE'],
'leng'		=> $row['DATA_LENGTH'],
'null'		=> $row['NULLABLE'] === 'Y',
'default'	=> $row['DATA_DEFAULT'],
'primary'	=> $row['IS_PRIMARY_KEY'] === 'Y',
'serial'	=> false,
'extra'		=> null
];
}
return $columns;
}
private static function parseDataType($type){
$pos = strpos($type, '(');
return $pos !== false ? substr($type, 0, $pos) : $type;
}
private static function parseLength($type){
if( preg_match('/\((\d+)\)/', $type, $matches) ){
return (int)$matches[1];
}
return null;
}
private static function parseInformixDataType($coltype){
$types = [
0 => 'CHAR',
1 => 'SMALLINT',
2 => 'INTEGER',
3 => 'FLOAT',
4 => 'SMALLFLOAT',
5 => 'DECIMAL',
6 => 'SERIAL',
7 => 'DATE',
8 => 'MONEY',
9 => 'NULL',
10 => 'DATETIME',
11 => 'BYTE',
12 => 'TEXT',
13 => 'VARCHAR',
14 => 'INTERVAL',
15 => 'NCHAR',
16 => 'NVARCHAR',
17 => 'INT8',
18 => 'SERIAL8',
19 => 'SET',
20 => 'MULTISET',
21 => 'LIST',
22 => 'ROW',
23 => 'COLLECTION',
40 => 'LVARCHAR',
41 => 'BLOB',
42 => 'CLOB',
43 => 'BOOLEAN'
];
$baseType = $coltype % 256;
return $types[$baseType] ?? 'UNKNOWN';
}
public static function info($tableName, $schema = null){
$driver = SS::getDriver();				        // $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
switch( $driver ){
case 'mysql':
return self::getMySQLTableInfo($tableName);
case 'pgsql':
return self::getPostgreSQLTableInfo($tableName, $schema);
case 'informix':
return self::getInformixTableInfo($tableName, $schema);
case 'oci':
return self::getOracleTableInfo($tableName, $schema);
default:
return [];
}
}
private static function getMySQLTableInfo($tableName){
$sql = "SHOW TABLE STATUS LIKE '{$tableName}'";
return SS::selectOne($sql, [], 9);
}
private static function getPostgreSQLTableInfo($tableName, $schema = 'public'){
$sql = "
SELECT
schemaname,
tablename,
tableowner,
hasindexes,
hasrules,
hastriggers
FROM pg_tables
";
return SS::selectOne($sql, ['table_name' => $tableName, 'schema' => $schema], 9);
}
private static function getInformixTableInfo($tableName, $schema = null){
$whereSchema = $schema ? "AND owner = '{$schema}'" : "";
$sql = "
SELECT
tabname,
owner,
tabtype,
ncols,
created
FROM systables
WHERE tabname = '{$tableName}' {$whereSchema}
";
return SS::selectOne($sql, [], 9);
}
private static function getOracleTableInfo($tableName, $schema = null){
$whereSchema = $schema ? "AND owner = '{$schema}'" : "AND owner = USER";
$sql = "
SELECT
table_name,
owner,
tablespace_name,
num_rows,
blocks,
avg_row_len,
last_analyzed
FROM all_tables
WHERE table_name = '{$tableName}' {$whereSchema}
";
return SS::selectOne($sql, [], 9);
}
public static function list($schema = null){
$driver = SS::getDriver();				        // $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
switch( $driver ){
case 'mysql':
$sql = "SHOW TABLES";
break;
case 'pgsql':
$schema = $schema ?? 'public';
$sql = "SELECT tablename FROM pg_tables WHERE schemaname = '{$schema}'";
break;
case 'informix':
$whereSchema = $schema ? "WHERE owner = '{$schema}'" : "";
$sql = "SELECT tabname FROM systables WHERE tabtype = 'T' {$whereSchema}";
break;
case 'oci':
$whereSchema = $schema ? "WHERE owner = '{$schema}'" : "WHERE owner = USER";
$sql = "SELECT table_name FROM all_tables {$whereSchema}";
break;
default:
throw new Exception("Driver no soportado: {$driver}");
}
SS::query($sql, [], 9);
$tables = [];
while( $row = SS::get("num", 9) ){
$tables[] = $row[0];
}
return $tables;
}
public static function indexes(string $table, string $schema = null): array {
$driver = ucfirst( SS::getDriver() );
$methodName = "get_{$driver}_Indexes";
if (!method_exists(self::class, $methodName)) {
throw new InvalidArgumentException("Controlador '$driver' no soportado");
}
return self::$methodName($table, $schema);
}
private static function get_Mysql_Indexes(string $table): array {
$sql = "SHOW INDEX FROM `$table`";
$results = SS::selectAll($sql, [], 9);
$indexes = [];
foreach($results as $row){
$indexName = $row['Key_name'];
if (!isset($indexes[$indexName])) {
$indexes[$indexName] = [
'name'    => $indexName,
'type'    => $row['Index_type'],
'unique'  => $row['Non_unique'] == 0,
'primary' => $indexName === 'PRIMARY',
'columns' => []
];
}
$indexes[$indexName]['columns'][] = [
'name'      => $row['Column_name'],
'position'  => $row['Seq_in_index'],
'collation' => $row['Collation'],
'null'      => $row['Null'] === 'YES'
];
}
return array_values($indexes);
}
private static function get_Pgsql_Indexes(string $table, string $schema = 'public'): array {
$sql = "
SELECT
i.relname AS index_name,
i.relkind,
ix.indisunique AS is_unique,
ix.indisprimary AS is_primary,
a.attname AS column_name,
ix.indkey,
a.attnum,
am.amname AS index_type
FROM pg_class t
JOIN pg_index ix ON t.oid = ix.indrelid
JOIN pg_class i ON i.oid = ix.indexrelid
JOIN pg_attribute a ON a.attrelid = t.oid
JOIN pg_am am ON i.relam = am.oid
WHERE t.relname = :table
AND t.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = :schema)
AND a.attnum = ANY(ix.indkey)
ORDER BY i.relname, a.attnum
";
$results = SS::selectAll($sql, ['table' => $table, 'schema' => $schema], 9);
$indexes = [];
foreach($results as $row){
$indexName = $row['index_name'];
if (!isset($indexes[$indexName])) {
$indexes[$indexName] = [
'name'   => $indexName,
'type'   => $row['index_type'],
'unique' => $row['is_unique'] === 't',
'primary'=> $row['is_primary'] === 't',
'columns'=> []
];
}
$indexes[$indexName]['columns'][] = [
'name' => $row['column_name'],
'position' => $row['attnum']
];
}
return array_values($indexes);
}
private static function get_Informix_Indexes(string $table, string $schema = null): array {
$sql = "
SELECT
i.idxname AS index_name,
i.idxtype AS index_type,
i.clustered,
i.part1, i.part2, i.part3, i.part4, i.part5,
i.part6, i.part7, i.part8, i.part9, i.part10,
i.part11, i.part12, i.part13, i.part14, i.part15,
i.part16,
c.colname AS column_name,
c.colno
FROM sysindexes i
JOIN systables t ON i.tabid = t.tabid
JOIN syscolumns c ON c.tabid = t.tabid
WHERE t.tabname = ?
";
$params = [$table];
if( $schema ){
$sql .= " AND t.owner = ?";
$params[] = $schema;
}
$sql .= " ORDER BY i.idxname, c.colno";
$results = SS::selectAll($sql, $params, 9);
$indexes = [];
foreach($results as $row){
$indexName = trim($row['index_name']);
if( !isset($indexes[$indexName]) ){
$indexes[$indexName] = [
'name'      => $indexName,
'type'      => $row['index_type'] == 'U' ? 'UNIQUE' : 'REGULAR',
'unique'    => $row['index_type'] == 'U',
'primary'   => false,                           // Se determina por convención
'clustered' => $row['clustered'] == 'Y',
'columns'   => []
];
}
for($i = 1; $i <= 16; $i++){
$partField = "part{$i}";
if( !empty($row[$partField]) ){
$indexes[$indexName]['columns'][] = [
'name'          => $row['column_name'],
'position'      => $i,
'column_number' => $row[$partField]
];
}
}
}
return array_values($indexes);
}
private static function get_Oci_Indexes(string $table, string $schema = null): array {
$sql = "
SELECT
i.index_name,
i.index_type,
i.uniqueness,
ic.column_name,
ic.column_position,
ic.descend,
CASE WHEN c.constraint_type = 'P' THEN 'Y' ELSE 'N' END AS is_primary
FROM user_indexes i
JOIN user_ind_columns ic ON i.index_name = ic.index_name
LEFT JOIN user_constraints c ON i.index_name = c.index_name AND c.constraint_type = 'P'
WHERE i.table_name = UPPER(?)
";
$params = [$table];
if ($schema) {
$sql = str_replace('user_indexes'	 , 'all_indexes'	, $sql);
$sql = str_replace('user_ind_columns', 'all_ind_columns', $sql);
$sql = str_replace('user_constraints', 'all_constraints', $sql);
$sql .= " AND i.owner = UPPER(?)";
$params[] = $schema;
}
$sql .= " ORDER BY i.index_name, ic.column_position";
$results = SS::selectAll($sql, $params, 9);
$indexes = [];
foreach($results as $row){
$indexName = $row['INDEX_NAME'];
if( !isset($indexes[$indexName]) ){
$indexes[$indexName] = [
'name'    => $indexName,
'type'    => $row['INDEX_TYPE'],
'unique'  => $row['UNIQUENESS'] === 'UNIQUE',
'primary' => $row['IS_PRIMARY'] === 'Y',
'columns' => []
];
}
$indexes[$indexName]['columns'][] = [
'name'	   => $row['COLUMN_NAME'],
'position' => $row['COLUMN_POSITION'],
'descend'  => $row['DESCEND'] === 'DESC'
];
}
return array_values($indexes);
}
}