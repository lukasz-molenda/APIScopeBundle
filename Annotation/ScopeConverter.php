<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 17:02
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ScopeConverter extends Annotation
{
	/** @var string */
	public $value = 'scopeCollection';

	/** @var string */
	public $queryString = 'with';

	public function getValue(): string
	{
		return $this->value;
	}

	public function getQueryString(): string
	{
		return $this->queryString;
	}
}