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


use BartB\APIScopeBundle\DependencyInjection\Configuration;
use BartB\APIScopeBundle\Exception\ScopeConfigException;

class ApiScopeConfigReader
{

	/** @var array */
	private $config;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	public function getAlwaysIncludedForRoute(string $route): array
	{
		$routeScopeConfig = $this->getForRoute($route);

		return $routeScopeConfig[Configuration::ALWAYS_INCLUDED];
	}

	public function getMapForRoute(string $route): array
	{
		$routeScopeConfig = $this->getForRoute($route);

		if (false === array_key_exists(Configuration::SUPPORTED_KEY_MAP, $routeScopeConfig))
		{
			return [];
		}

		return $routeScopeConfig[Configuration::SUPPORTED_KEY_MAP];
	}

	public function getMapSecurityForMapRoute(string $route, string $externalScopeName): string
	{
		$routeScopeConfig = $this->getForRoute($route);

		if (false === array_key_exists(Configuration::SUPPORTED_KEY_MAP, $routeScopeConfig))
		{
			return '';
		}

		if (false === array_key_exists($externalScopeName, $routeScopeConfig[Configuration::SUPPORTED_KEY_MAP]))
		{
			return '';
		}

		if (false === array_key_exists(Configuration::SUPPORTED_KEY_MAP_SECURITY, $routeScopeConfig[Configuration::SUPPORTED_KEY_MAP][$externalScopeName]))
		{
			return '';
		}

		return $routeScopeConfig[Configuration::SUPPORTED_KEY_MAP][$externalScopeName][Configuration::SUPPORTED_KEY_MAP_SECURITY];
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