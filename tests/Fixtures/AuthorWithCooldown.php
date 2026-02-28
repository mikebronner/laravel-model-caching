<?php

namespace GeneaLabs\LaravelModelCaching\Tests\Fixtures;

class AuthorWithCooldown extends Author
{
    protected $table = 'authors';

    protected $cacheCooldownSeconds = 1;
}
