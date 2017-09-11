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


use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ScopeSecurityException extends UnauthorizedHttpException
{
	public static function authorizationFails(string $scope): self
	{
		$msg = sprintf('You are not authorized to use `%s` scope', $scope);

		return new static($msg);
	}
}