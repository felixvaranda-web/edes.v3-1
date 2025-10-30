<?PHP
class Check {
public  static $paramSystem = [
"_ACCESS"		=> '/^[0-9]+\/[0-9]+$/'   // 24/0
,"_ASIGN"		=> "a|m"                  //toDo: ojo falta una "S"
,"_ASSIGN"		=> "|a|b|c|m|l"
,"_AUX_"		=> "|C|S"
,"_BACKGROUND"	=> "1"
,"_BAK"			=> "1"
,"_CACHE_"		=> "{i}"
,"_CACHEFILESRV"=> "{}"
,"_CALL"		=> "1"
,"_CAMPO_"		=> '/^(|[\w]+)$/'
,"_CONTEXT"	=> "{i}"         //toDo: tarea: eliminar _CONTEXT
,"_CLOSE_"		=> "1"
,"_DB"			=> '/^[a-z_\'\"\.]{0,20}*$/'
,"_DESTINO_"	=>  '/^[a-zA-Z0-9_]{1,40}$/'
,"_E_X_P__MD5"	=> "1"
,"_FIELDSLIST"	=> '/ ^ [a-zA-Z0-9,_\|]{1,3000}$/'
,"_FIELDSWITHFILES" => '/^[a-zA-Z0-9_\|]{1,60}$/'
,"_FILENAME"	=> '/^[a-zA-Z0-9\s._]{1,60}$/'
,"_FILEPDF"		=> "{}"  // vacío
,"_FILTRO"		=> "{s,1,400}"
,"_FORMBUTTONS"	=> '/^[IVvDdUuW*-s]{1,11}$/'  // cambio de modo en las tarjetas - "{s,0,8}",
,"_GRAPHS"		=> "{}"
,"_HA"		=> "1" // ??
,"_IMPORTARDATOS" => "{}"
,"_ISLOP"		=> '/^[,-DIMRTUVabcm]{1,15}$/'  //modo de las isublists "mR"/"cR,V-TM"/"mR,ID"
,"_ISUBLIST"	=> "|1"
,"_ISUBLISDTELETE"=> '/^[a-zA-Z0-9._\/\|]{1,60}$/'
,"_ISUBLISTSERIAL"=> '/^[a-zA-Z0-9,_-\/\|\']{1,200}$/'
,"_MD5"			=> "{t,md5}"
,"_MODE"		=> "V"
,"_MULTI_"		=> "{}"
,"_NOBUTTON"	=> "|1"
,"_NODDBB"		=> "{}"
,"_NOEVENT"		=> "0|1"
,"_OFFLABEL"	=> "|-título"
,"_OPC"			=> "A"
,"_ORDER_"		=> '/^[a-zA-Z0-9_,*]{1,60}$/'
,"_ORDEN__TABLE"=> "{}"
,"_PASTE"		=> "1"
,"_PSCRIPT"		=> '/^[a-zA-Z_.:/]+$/'
,"_PSOURCE"		=> '/^[a-zA-Z_.\/\"]*$/'
,"_REG_"		=> '/^[PR][><IF]\d+$/'
,"_SAVEDATALIST"=> '/^[a-zA-Z0-9\.\s\|~=\'\[\]]{1,1000}$/' // puede tener 2000 chr
,"_SCRIPT"		=> '/^[a-zA-Z0-9\.\/]{5,50}$/'
,"_SEEK"	=> "{s,0,1}"
,"_SESS"	=> "{t,hex,4}"  //??
,"_SESS_"	=> "{s,6}"
,"_SEL_"		=> '/^[a-zA-Z0-9\s,;=_\(\)]{0,250}$/'
,"_SIZE"		=> '/^-?(\d+%?|\d+R?),-?(\d+%?|\d+R?)$/'
,"_SIZE_"		=> "{i,1,1000}"
,"_SOURCE"		=> "{s,0,60}"
,"_STOP"		=> "|1|verdadero"
,"_SUBINSERT"=> ""
,"_SUBLISTED_"	=> "1|verdadero"
,"_tiempo"	=> "{i}"  //??
,"_TITULO"		=> "{s,1,60}"
,"_CARGAR_ARCHIVO_1" => "{s,0,60}"
,"_CARGAR_ARCHIVO_2" => "{s,0,60}"
,"_CARGAR_ARCHIVO_3" => "{s,0,60}"
,"_VISIBLE_ROWS_"=> "{0,1,4}"
];
public static $allRules;
public static $keyCheck;
public static $valueCheck;
public static $rule;
public static function get($allRules){
if( empty($allRules) ){
return;
}
self::$allRules = $allRules;
$queryString = $_SERVER["QUERY_STRING"];
$pattern = '/^R:\$[^\.$.&]+\.(?:js|css|png|jpg|jpeg|gif)(?:&.*)?$/';
if( preg_match($pattern, $queryString) ){
return;
}
$queryString .= "&";
$rules = null;
foreach($allRules as $key => $value){
if( strpos($queryString, "&{$key}&") !== false ){
list($nameVar, $valueVar) = explode("=", $key);
if( $_GET[$nameVar] == $valueVar ){
$rule = $value;
break;
}
}
$rules = $value;
}
$numParan = 0;
$optional = array();
foreach($_GET as $key => $value){
if( ++$numParan == 1 ) continue;
if( substr($key,-1)=="?" ){
$key = trim(substr($key, 0, -1));
$optional[$key] = true;
}
if( isset($rules[$key]) ){
$rule = $rules[$key];
}else if (isset(self::$paramSystem[$key]) ){
$rule = self::$paramSystem[$key];
if( empty($rule) ){
continue;
}
}else{
self::error("Parámetro no permitido \"{$key}\"");
}
self::checkParam($key, $value, $rule);
}
foreach($rules as $key => $value){
if( substr($key,-1)=="?" ){
$key = trim(substr($key, 0, -1));
$optional[$key] = true;
}
if( !isset($_GET[$key]) && !isset($optional[$key]) ){
self::error("Parámetro obligatorio inexistente \"{$key}\"");
}
}
return true;
}
public static function checkParam($key, $value, $rule){
self::$keyCheck = $key;
self::$rule = $rule;
self::$valueCheck = $value;
if( empty($value) ){
if( $rule[0] == "|" || substr($rule, 0, 4) == "{s,0" ){
return;
}
}
$multicondition = array('>', '<', '=', '!', '(', ')');
$valueBak = $value;
if( $rule[0] == "{" ){
$rule = str_replace(array(" ", "\t"), "", $rule);
}
if( $rule[0] == "/" ){
if( preg_match($rule, $_GET[$key]) ){
return true;
}
return self::error("No cumple con la expresión regular");
}else if( $rule[0] == "(" ){
}else if( $rule[0] == ")" ){
}else if( $value == $rule ){
return true;
}else{
if( $rule[0] != "{" && strpos($rule, '|') !== false ){
$ok = preg_match("/^({$rule})$/", $_GET[$key]);
if( !$ok ){
return self::error("Constante no permitida");
}
return true;
}
}
if( !empty($valueBak) && in_array($valueBak[0], $multicondition) ){
if( $valueBak[0] == "(" || $valueBak[0] == ")" ){
$dimValue = explode(",", substr($valueBak, 1, -1));
}else{
$patron = '/>=|<=|<>|!=|>|<|=/'; // $patron = '/(>=|<=|<>|!=|>|<|=)/';
$dimValue = preg_split($patron, $valueBak, -1, PREG_SPLIT_NO_EMPTY);
}
}else{
$dimValue = array($valueBak);
}
for($i=0; $i < count($dimValue); $i++){
$valueGet = $dimValue[$i];
self::$valueCheck = $valueGet;
switch( $rule ){
case "{}": // cadena vacía
if( !empty($valueGet) ){
return self::error("Valor no permitida");
}
break;
case "{t}": // timestamp
self::isTimestamp($valueGet);
break;
case "{d}": // date
self::isDate($valueGet);
break;
case "{p}": // period
self::isPeriod($valueGet);
break;
case "{h}": // hour
self::isHour($valueGet);
break;
case "{b}": // boolean
self::isBoolean($valueGet);
break;
case "{@}": // email
if( filter_var($valueGet, FILTER_VALIDATE_EMAIL) === false ){
return self::error("El dato no es un \"email\"");
}
break;
case "{w}": // dirección web
if( filter_var($valueGet, FILTER_VALIDATE_URL) === false ){
return self::error("El dato no es una \"url\"");
}
break;
default:
if( $rule[0] != "{" ){
if( strpos($valueGet, '|') !== false ){
$ok = preg_match("/^(.*\|)?" . preg_quote($valueGet, '/') . "(\|.*)?$/", $valueGet);
}else{
$ok = $valueGet == $rule;
}
if( !$ok ){
return self::error("Constante no permitida");
}
break;
}
$rule = substr($rule, 1, -1);
list($op) = explode(",", $rule);
$isNegative = false;
if( strpos($op, "-") !== false ){
$isNegative = true;
$op = str_replace("-", "", $op);
}
switch( $op ){
case "0": // cadena de números
if( !empty($valueGet) && !ctype_digit($valueGet) ){
return self::error("El valor no es del tipo \"number\"");
}
if( $rule == "0" ){
$rule = "s,1,20";
}
list(, $min, $max) = explode(",", $rule);
if( strpos($min, "|") !== false ){
$item = explode("|", $min);
for($i=0; $i<count($item); $i++){
if( strlen($valueGet) == ((int)$item[$i]) ){
continue 2;
}
}
return self::error("Longitud no permitida en \"number\"");
}
if( empty($max) ){
$max = $min;
}
if( strlen($valueGet) < $min || strlen($valueGet) > $max ){
return self::error("Longitud no permitida en \"number\"");
}
break;
case "i": // integer
if( filter_var($valueGet, FILTER_VALIDATE_INT) === false ){
return self::error("El valor no es del tipo \"integer\"");
}
$valueBak = $valueGet;
if( (string)$valueGet != (string)($valueGet * 1) ){
return self::error("El valor no es del tipo \"integer\"");
}
$valueGet *= 1;
if( !is_integer($valueGet) ){
return self::error("El valor no es del tipo \"integer\"");
}
list(, $min, $max) = explode(",", $rule);
if( isset($min) && $valueGet < $min * 1 ){
return self::error("Valor inferior al mínimo");
}
if( isset($max) && $valueGet > $max * 1 ){
return self::error("Valor superior al máximo");
}
if( !$isNegative && $valueGet<0 ){
return self::error("El valor no puede ser negativo");
}
break;
case "f": // float, ojo si tiene decimales acabados en cero al multiplicar y hacer (string) no sale bien o mandan un integer?
if( filter_var($valueGet, FILTER_VALIDATE_FLOAT) === false ){
return self::error("El tipo de dato no es un \"float\"");
}
$valueBak = $valueGet;
if( (string)$valueGet != (string)($valueGet * 1) ){
return self::error("El tipo de dato no es un \"float\"");
}
$valueGet *= 1;
if( !is_float($valueGet) ){
return self::error("El tipo de dato no es un \"float\"");
}
list(, $min, $max) = explode(",", $rule);
if( isset($min) && $valueGet < $min * 1 ){
return self::error("Error en el valor mínimo");
}
if( isset($max) && $valueGet > $max * 1 ){
return self::error("Error en el valor máximo");
}
if( !$isNegative && $valueGet<0 ){
return self::error("El valor no puede ser negativo");
}
break;
case "v": // variable de sesión
list(, $varSession) = explode(",", $rule);
$varSession = trim($varSession);
$optional = false;
if( !isset($_SESSION[$varSession]) ){
return self::error("No existe la variable \"sesión\"");
}
if( $_SESSION[$varSession] != $valueGet ){
return self::error("Valor de la variable de sesión no permitida"); // [{$varSession}] en [{$key}]=[{$value}] / [{$valueGet}] _SESSION[{$_SESSION[$varSession]}]
}
break;
case "s": // string
if( $rule == "s" ){
$rule = "s,1,256";
}
list(, $min, $max) = explode(",", $rule);
if( strpos($min, "|") !== false ){
$item = explode("|", $min);
for($i=0; $i<count($item); $i++){
if( strlen($valueGet) == ((int)$item[$i]) ){
continue 2;
}
}
return self::error("Longitud no permitida en \"string\"");
}
if( empty($max) ){
$max = $min;
}
if( strlen($valueGet) < $min || strlen($valueGet) > $max ){
return self::error("Longitud no permitida"); // .implode(",", $cond).' > '.strlen($valueGet) del parámetro [{$key}]=[{$valueGet}] / min[{$min}] max[{$max}]
}
break;
case "t": // type data
list(, $type, $min, $max) = explode(",", $rule);
if( $type == "hex" ){
if( empty($min) ){
$min = 7;
}
if( !(bool) preg_match('/^[#0-9a-fA-F]{' . $min . '}$/', $valueGet) ){ // Uno o más caracteres hexadecimales (no permite cadenas vacías)
return self::error("Tipo de dato \"{$key}\"");
}
}else if( $type == "md5" ){
if( !(bool) preg_match('/^[a-fA-F0-9]{32}$/', $valueGet) ){ // Uno o más caracteres hexadecimales (no permite cadenas vacías)
return self::error("Tipo de dato \"{$key}\"");
}
}else{
$dimType = array(
"int"	=> FILTER_VALIDATE_INT,
"float"	=> FILTER_VALIDATE_FLOAT,
"email"	=> FILTER_VALIDATE_EMAIL,
"@"		=> FILTER_VALIDATE_EMAIL,
"url"	=> FILTER_VALIDATE_URL,
"ip"	=> FILTER_VALIDATE_IP
);
$isNegative = false;
if( strpos($type, "-") !== false ){
$isNegative = true;
$type = str_replace("-", "", $type);
}
if( filter_var($valueGet, $dimType[$type]) === false ){
return self::error("El dato no coincide son su tipo \"{$key}\"");
}
if( $type=="url" && substr($valueGet,0,7)!="http:/"."/" && substr($valueGet,0,8)!="https:/"."/" ){
return self::error("El dato no coincide son su tipo \"{$key}\"");
}
}
if( isset($min) || isset($max) ){
if( preg_match('/^(int|float)$/', $type) ){
$valueGet *= 1;
if( isset($min) && $valueGet < $min * 1 ){
return self::error("Valor mínimo");
}
if( isset($max) && $valueGet > $max * 1 ){
return self::error("Valor máximo");
}
}else{
if( isset($min) && strlen($valueGet) < $min ){
return self::error("La longitud del dato es muy pequeña"); // .implode(",", $cond).' > '.strlen($valueGet) [{$key}]=[{$valueGet}] / min[{$min}] max[{$max}]
}
if( isset($max) && strlen($valueGet) > $max ){
return self::error("La longitud del dato es muy grande"); // .implode(",", $cond).' > '.strlen($valueGet) [{$key}]=[{$valueGet}] / min[{$min}] max[{$max}]
}
}
}
if( !$isNegative && ($type=="int" || $type=="float") && $valueGet < 0 ){
return self::error("El valor no puede ser negativo");
}
break;
default:
return self::error("Regla \"{$rule}\" desconocida"); // [{$key}]=[{$value}] / [{$valueGet}]
}
}
}
return true;
}
private static function error($str){
S::error(
$str
,"[" . self::$keyCheck . "]=[" . self::$valueCheck . "]"
,"RULES"
,self::$allRules
);
return false;
}
public static function isDate($fecha, $stop = true){
if( strlen($fecha) != 10 ){
if (!$stop) return false;
return self::error("Longitud en la fecha");
}
if( !preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $fecha, $matches) ){
if (!$stop) return false;
return self::error("Formato erroneo de la fecha");
}
$year = $matches[1];
$month = $matches[2];
$day = $matches[3];
if( $month < 1 || $month > 12 || $day < 1 || $day > 31 ){
if (!$stop) return false;
return self::error("Contenido de la fecha");
}
try{
$date = new DateTime($fecha);
if( $date->format("Y-m-d") == "{$year}-{$month}-{$day}" ){
return true;
}
if( !$stop ) return false;
return self::error("Fecha inválida");
}catch(Exception $e){
if( !$stop ) return false;
return self::error("Fecha inválida \"" . $e->getMessage() . "\"");
}
}
public static function isHour($time, $stop = true){
if( !preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $time, $matches) ){
if( !$stop ){
return false;
}
return self::error("Formato de hora");
}
$item = explode(":", $time);
for($i = 0; $i < 3; $i++){
if( (int)$item[$i] > 59 ){
return self::error("Formato de hora");
}
}
return true;
}
public static function isPeriod($period){
if( !self::isDate($period . "-01", false) ){
return self::error("Formato \"period\"");
}
return true;
}
public static function isTimestamp($instant){
list($date, $time) = explode(" ", $instant);
if( !self::isDate($date) || !self::isHour($time, false) ){
return self::error("Formato \"timestamp\"");
}
return true;
}
public static function isBoolean($value){
if( gettype($value) == "boolean" ){
return true;
}
if( !in_array($value . "", array('1', '0', 'true', 'false'), true) ){
return self::error("No es del tipo \"booleano\"");
}
return true;
}
public static function addRules($mode, $DBINDEX, $_pField, $rulesGET){
list($action) = explode(":", $_SERVER["QUERY_STRING"]);
if( $_SERVER['REQUEST_METHOD'] != 'GET' 		||
in_array($mode, array('c', 'b', 'm', 'a'))	||
in_array($action, ['Lcl', 'Lml', 'Lbl'])
){
return;
}
$binary = array(
"T"   => "{0,9}"
,"0"   => "{0}"
,"CP"  => "{0,5}"
,"CDI" => "{t}"
,"F4"  => "{d}"
,"P4"  => "{p}"
,"*"   => "{i}"
,"+"   => "{i}"
,"-"   => "{i-}"
,"+,"  => "{f}"
,"-,"  => "{f-}"
,"CLR" => "{t,hex}"
,"IP"  => "{t,ip}"
,"@"   => "{@}"
,"W"   => "{t,url}" // Dirección de web, si el nombre del campo es "facebook" o "twitter" pondrá su logo particular.
);
if( empty($rulesGET) ){
$rulesGET[""] = [];
}
$items = explode(",", $DBINDEX);
for($i = 0; $i < count($items); $i++){
$index = $items[$i];
if( !isset($_pField[$index]) ){
continue;
}
$tc = $_pField[$index][2];
if( isset($binary[$tc]) ){
if( $binary[$tc]=="{0}" && ($_pField[$index][8] == "#" || $_pField[$index][8] == "=")  ){
if( $_pField[$index][8] == "=" ){
$ruleStr = "{0,{$_pField[$index][4]}}";
}else{
$ruleStr = "{0,1,{$_pField[$index][4]}}";
}
}else{
$ruleStr = $binary[$tc];
}
}else{
$min = 0;
$max = $_pField[$index][4];
if( $_pField[$index][8] == "#" ){
$min++;
}else if( $_pField[$index][8] == "=" ){
$min = $_pField[$index][4];
}
$ruleStr = "{s,{$min},{$max}}";
}
$rulesGET[""][$index] = $ruleStr;
}
return $rulesGET;
}
}