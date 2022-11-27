<?php declare(strict_types=1);

enum ActionOperation: string
{
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';
}