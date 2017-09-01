<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 17:09
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Exception;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ScopeReaderException extends BadRequestHttpException
{
	public static function scopeIsNotSupported(string $scope): self
	{
		$msg = sprintf('`%s` scope is not supported ', $scope);

		return new static($msg);
	}
}