<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 17:07
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Data;


class ScopeCollection
{
	/** @var  string[] */
	private $scopes = [];

	public function getScopes(): array
	{
		return $this->scopes;
	}

	public function setScopes(array $scopes = null): self
	{
		$this->scopes = (array) $scopes;

		return $this;
	}

	public function addScope(string $scopes): self
	{
		$this->scopes[] = $scopes;

		return $this;
	}

	public function addScopes(array $scopes): self
	{
		$this->scopes = array_merge($this->scopes, $scopes);

		return $this;
	}
}