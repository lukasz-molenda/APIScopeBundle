<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 02.09.2017
 * Time: 09:49
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Tests\Annotation;


use BartB\APIScopeBundle\Annotation\ScopeConverter;
use BartB\APIScopeBundle\Data\ScopeCollection;

class DummyController
{
	/**
	 * @ScopeConverter()
	 */
	public function getDummyAction(ScopeCollection $scopeCollection)
	{

	}

	public function __invoke()
	{
		return [$this, 'getDummyAction'];
	}
}