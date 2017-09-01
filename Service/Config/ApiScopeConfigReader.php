<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 16:26
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Service\Config;



use BartB\APIScopeBundle\Exception\ScopeConfigException;

class ApiScopeConfigReader
{
	const ALWAYS_INCLUDED_CONFIG_KEY = 'always_included';
	const SUPPORTED_KEY_MAP          = 'supported_key_map';

	/** @var array */
	private $config;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function getAlwaysIncludedForRoute(string $route): array
	{
		$routeScopeConfig = $this->getForRoute($route);

		return $routeScopeConfig[self::ALWAYS_INCLUDED_CONFIG_KEY];
	}

	public function getMapForRoute(string $route): array
	{
		$routeScopeConfig = $this->getForRoute($route);

		if (false === array_key_exists(self::SUPPORTED_KEY_MAP, $routeScopeConfig))
		{
			return [];
		}

		return $routeScopeConfig[self::SUPPORTED_KEY_MAP];
	}

	private function getForRoute(string $route): array
	{
		if (false === array_key_exists($route, $this->config))
		{
			throw ScopeConfigException::noConfigFoundForRoute($route);
		}

		return $this->config[$route];
	}
}