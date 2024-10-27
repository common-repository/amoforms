<?php
namespace Amoforms\Libs\Errors\Sentry;

defined('AMOFORMS_BOOTSTRAP') or die('Direct access denied');

use Amoforms\Exceptions\Base;

/**
 * Class Client
 * @since 2.19.10
 * @package Amoforms\Libs\Errors\Sentry
 */
class Client extends \Raven_Client
{
	/**
	 * @param \Exception|null $exception - protection from sending empty or unneeded exceptions
	 * @param $culprit_or_options
	 * @param $logger
	 * @param $vars
	 * @return string|null - event_id | NULL
	 */
	public function captureException($exception, $culprit_or_options = NULL, $logger = NULL, $vars = NULL)
	{
		$result = NULL;

		if ($this->is_amoforms_exception($exception)) {
			$result = parent::captureException($exception, $culprit_or_options, $logger, $vars);
		}

		return $result;
	}

	/**
	 * @param \Exception $exception
	 * @return bool
	 */
	protected function is_amoforms_exception($exception)
	{
		if (!is_object($exception) || !($exception instanceof \Exception)) {
			return FALSE;
		}
		if ($exception instanceof Base) {
			return TRUE;
		}

		if ($this->is_amoforms_path($exception->getFile())) {
			return TRUE;
		}

		$count = 0;
		foreach ($exception->getTrace() as $action) {
			if (isset($action['file']) && $this->is_amoforms_path($action['file'])) {
				return TRUE;
			}
			if (++$count > 5) {
				break;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	protected function is_amoforms_path($path)
	{
		return strpos($path, '/plugins/amoforms/') !== FALSE;
	}

	/**
	 * Small http info
	 * @return array
	 */
	protected function get_http_data()
	{
		$data = parent::get_http_data();
		$result = [
			'request' => [
				'method'       => $data['request']['method'],
				'url'          => $data['request']['url'],
				'query_string' => $data['request']['query_string'],
			],
		];
		if(isset($data['request']['data'])){
			$result['request']['data'] = $data['request']['data'];
		}

		return $result;
	}
}
