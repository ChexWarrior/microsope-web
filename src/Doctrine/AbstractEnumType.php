<?php declare(strict_types=1);

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractEnumType extends Type
{
    abstract public static function getEnumsClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'TEXT';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (enum_exists($this->getEnumsClass(), true) === false) {
            throw new \LogicException('This class must be an enum!');
        }

        return $this::getEnumsClass()::tryFrom($value);
    }
}
