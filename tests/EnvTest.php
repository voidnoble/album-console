<?php

class EnvTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertEquals('production', $this->app->environment(), 'Notice: If not fail then environment is in the production');
    }
}
