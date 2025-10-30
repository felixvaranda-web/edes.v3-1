<?PHP
class RedisSessionManager {
private static $redis;
private static $prefix = 'SESSION:';
private static $suffix = '_SUFFIX';
private static $ttl = 1440;                     // 24 minutos por defecto
public static function configure(
string $host = '127.0.0.1',
int $port = 6379,
?string $auth = null,
string $prefix = 'SESSION:',
string $suffix = '_SUFFIX',
int $ttl = 1440): void {
self::$redis = new Redis();
self::$redis->connect($host, $port);
if( $auth !== null ){
self::$redis->auth($auth);
}
self::$prefix = $prefix;
self::$suffix = $suffix;
self::$ttl = $ttl;
}
public static function init(): void {
$handler = new class implements SessionHandlerInterface {
public function open($savePath, $sessionName): bool {
return true;
}
public function close(): bool {
return true;
}
public function read($sessionId): string|false {
$key = RedisSessionManager::$prefix . $sessionId . RedisSessionManager::$suffix;
return RedisSessionManager::$redis->get($key) ?: '';
}
public function write($sessionId, $data): bool {
$key = RedisSessionManager::$prefix . $sessionId . RedisSessionManager::$suffix;
return RedisSessionManager::$redis->setex($key, RedisSessionManager::$ttl, $data);
}
public function destroy($sessionId): bool {
$key = RedisSessionManager::$prefix . $sessionId . RedisSessionManager::$suffix;
return (bool)RedisSessionManager::$redis->del($key);
}
public function gc($maxlifetime): int|false {
return 0;
}
};
session_set_save_handler($handler, true);
}
public static function setSuffix(string $suffix): void {
self::$suffix = $suffix;
}
public static function getCurrentSessionKey(): ?string {
return session_id() ? self::$prefix . session_id() . self::$suffix : null;
}
public static function getRedisInstance(): Redis {
return self::$redis;
}
}