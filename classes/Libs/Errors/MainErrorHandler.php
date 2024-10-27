<?php
namespace Amoforms\Libs\Errors;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Libs\Plugin\PluginSettings;
use Amoforms\Libs\Errors\Sentry\Client;
use Amoforms\Models\amoCRM\amoUser;
use Amoforms\Traits\Singleton;
use Raven_ErrorHandler;

/**
 * Class Sentry
 * @since 2.19.10
 * @method static MainErrorHandler instance
 * @package Amoforms\Libs\Errors
 */
class MainErrorHandler
{
	use Singleton;

	const SENTRY_DSN = 'https://7b30c38068bb4fafac7ceca741fb1c9b:290bb79ee2e54b43920f848153a9be7f@app.getsentry.com/67212';

	/**
	 * @var Client
	 */
	protected $_sentry_client;
	protected $_plugin_settings;

	protected function __construct()
	{
		$this->_sentry_client = new Client(self::SENTRY_DSN);
		$this->_plugin_settings = PluginSettings::instance();

		$user = amoUser::instance();
		$this->_sentry_client->set_user_data(1, $user->get_data('login'), $user->get_data());
		$this->_sentry_client->extra_context([
			'version' => AMOFORMS_VERSION,
			'plugin_uid' => $this->_plugin_settings->get(PluginSettings::KEY_PLUGIN_UID),
		]);

		$error_handler = new Raven_ErrorHandler($this->_sentry_client);
		$error_handler->registerExceptionHandler();
		$error_handler->registerErrorHandler();
		$error_handler->registerShutdownFunction();
	}

	/**
	 * @param \Exception $e
	 * @return string|null - event_id | NULL
	 */
	public function capture_exception($e)
	{
		return $this->_sentry_client->captureException($e);
	}

	/**
	 * @param string $message
	 * @param array $params
	 * @return null|string - event_id | NULL
	 */
	public function capture_message($message, array $params = [])
	{
		return $this->_sentry_client->captureMessage($message, $params);
	}

	/**
	 * @param array $data
	 * @return null|string - event_id | NULL
	 */
	public function handle_js_error(array $data)
	{
		$message = NULL;
		foreach (['message', 'msg'] as $key) {
			if (!empty($data[$key]) && is_string($data[$key])) {
				$message = trim($data[$key]);
				break;
			}
		}
		if (!$message) {
			return NULL;
		}

		$extra = [];
		foreach (['file', 'line', 'type', 'target', 'time', 'stack', 'data'] as $key) {
			if (isset($data[$key])) {
				$extra[$key] = $data[$key];
			}
		}

		$options = [
			'logger' => 'js',
			'extra'  => $extra,
		];

		foreach (['method', 'url', 'query_string'] as $key) {
			if (!empty($data['request'][$key])) {
				$options['request'][$key] = $data['request'][$key];
			}
		}

		return $this->_sentry_client->captureMessage($message, [], $options);
	}
}
