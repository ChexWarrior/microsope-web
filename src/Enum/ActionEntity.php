<?php declare(strict_types=1);

enum ActionEntity: string
{
    case History = 'history';
    case Event = 'event';
    case Period = 'period';
    case Player = 'player';
    case Scene = 'scene';
}