<?php

namespace App\Http\Games\PgSoft;



class Diaochan
{
  public $table;
  public $symbols;
  public $multiplier;
  public $lines;


  public function __construct()
  {

    $this->table = 15; // O número de símbolos distintos na tabela de pagamentos
    $this->symbols = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // índices dos símbolos
    $this->multiplier = [
      0 => [5 => 5000, 4 => 1000, 3 => 200, 2 => 10], // Wild
      1 => [5 => 500, 4 => 150, 3 => 30, 2 => 3], // Guan Yu
      2 => [5 => 300, 4 => 80, 3 => 25, 2 => 2], // Cao Cao
      3 => [5 => 200, 4 => 50, 3 => 20], // Lü Bu
      4 => [5 => 150, 4 => 40, 3 => 15], // A
      5 => [5 => 125, 4 => 30, 3 => 10], // K
      6 => [5 => 100, 4 => 20, 3 => 8], // Q
      7 => [5 => 75, 4 => 15, 3 => 5], // J
      8 => [5 => 50, 4 => 10, 3 => 3], // 10
      9 => [5 => 30, 4 => 5, 3 => 2], // 9
      // O 10º símbolo na sua tabela é provavelmente um scatter ou outro tipo especial que não tem multiplicador definido na imagem.
    ];

    $this->lines = [
      1 => [0, 3, 6, 9, 12],
      2 => [1, 4, 7, 10, 13],
      3 => [2, 5, 8, 11, 14],
      4 => [0, 4, 8, 10, 12],
      5 => [2, 4, 6, 10, 14],
      6 => [1, 3, 6, 9, 13],
      7 => [1, 5, 8, 11, 13],
      8 => [0, 3, 7, 9, 12],
      9 => [2, 5, 7, 11, 14],
      10 => [0, 4, 7, 10, 12],
      11 => [2, 4, 7, 10, 14],
      12 => [1, 3, 7, 9, 13],
      13 => [1, 5, 7, 11, 13],
      14 => [0, 3, 7, 9, 12],
      15 => [2, 5, 7, 11, 14],
      16 => [0, 4, 6, 10, 12],
      17 => [2, 4, 8, 10, 14],
      18 => [1, 4, 6, 10, 13],
      19 => [1, 4, 8, 10, 13],
      20 => [0, 3, 7, 10, 12],
      21 => [2, 5, 7, 10, 14],
      22 => [1, 3, 7, 10, 13],
      23 => [1, 5, 7, 10, 13],
      24 => [0, 3, 6, 10, 12],
      25 => [2, 5, 8, 10, 14],
      26 => [1, 3, 6, 10, 13],
      27 => [1, 5, 8, 10, 13],
      28 => [0, 4, 6, 9, 12],
      29 => [2, 4, 8, 11, 14],
      30 => [1, 4, 6, 9, 13],
    ];

  }

}