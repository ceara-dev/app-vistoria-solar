<?php

namespace App\Enums;

/**
 * Situação do deslocamento no ponto, com as mesmas siglas do app.
 */
enum TripPointStatus: string
{
    case Moving = 'M';
    case Paused = 'P';
}
