<?php
define('AMOFORMS_BOOTSTRAP', TRUE);
require_once __DIR__ . '/const.php';

spl_autoload_register(function ($class) {
	if (strpos($class, AMOFORMS_NS . '\\') === 0) {
		$path = str_replace(AMOFORMS_NS, AMOFORMS_CLASSES_DIR, $class);
		$path = str_replace('\\', '/', $path) . '.php';
		if (file_exists($path)) {
			/** @noinspection PhpIncludeInspection */
			require_once $path;
		}
	}
});

$vendors = [
	'\Raven_Autoloader'    => '/Raven/Autoloader.php',
	'\Bt51\NTP\Client'     => '/Bt51/Client.php',
	'\Bt51\NTP\Socket'     => '/Bt51/Socket.php',
	'\Mustache_Autoloader' => '/Mustache/Autoloader.php',
];
try {
	foreach ($vendors as $class => $path) {
		if (!class_exists($class)) {
			if (file_exists(AMOFORMS_VENDOR_DIR . $path)) {
				/** @noinspection PhpIncludeInspection */
				require_once AMOFORMS_VENDOR_DIR . $path;
			} else {
				throw new Exception("Vendor file doesn't exists");
			}
		}
	}

	Raven_Autoloader::register();
	Amoforms\Libs\Errors\MainErrorHandler::instance();
	Mustache_Autoloader::register();

	new Amoforms\Plugin();

} catch (\Exception $e) {
	if (class_exists('\Amoforms\Helpers')) {
		\Amoforms\Helpers::handle_exception($e);
	}
}
