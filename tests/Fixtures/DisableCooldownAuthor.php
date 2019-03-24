<?php namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

use GeneaLabs\LaravelModelCaching\Traits\DisableCooldown;

class DisableCooldownAuthor extends Author
{
	use DisableCooldown;
}
