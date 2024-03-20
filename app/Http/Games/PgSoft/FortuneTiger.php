<?php

namespace App\Http\Games\PgSoft;



class FortuneTiger
{
    public $table;
    public $symbols;
    public $multiplier;
    public $lines;
   

  public function __construct(){

    $this->table = 9;
        $this->symbols = [0, 2, 3, 4, 5, 6, 7];
        $this->multiplier = [
            0 => [3 => 250],
            2 => [3 => 100],
            3 => [3 => 25],
            4 => [3 => 10],
            5 => [3 => 8],
            6 => [3 => 5],
            7 => [3 => 3],
        ];

        $this->lines = [
            1 => [1, 4, 7],
            2 => [0, 3, 6],
            3 => [2, 5, 8],
            4 => [0, 4, 8],
            5 => [2, 4, 6],
        ];

  }

}