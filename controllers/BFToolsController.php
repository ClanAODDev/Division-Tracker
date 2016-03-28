<?php

class BFToolsController
{
    
    public function index()
    {
        Flight::render('tools/bf_battlelog', ['js' => 'bf_battlelog']);
    }

}