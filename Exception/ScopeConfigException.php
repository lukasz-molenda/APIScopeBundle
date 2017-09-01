<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 16:27
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Exception;


class ScopeConfigException extends \RuntimeException
{

	public static function noConfigFoundForRoute(string $route): self
	{
		$msg = sprintf('No scope config found for route %s', $route);

		return new static($msg);
	}

	public static function scopeIsNotSupported(string $scope): self
	{
		$msg = sprintf('Scope is not supported %s', $scope);

		return new static($msg);
	}

}