<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PassportTest extends \PassportTestCase
{
    use DatabaseTransactions;

    protected $scopes = ['restricted-scope'];

    public function testRestrictedRoute()
    {
        $this->get('/api/user')
            ->assertResponseStatus(401);
    }

}