<?php

declare(strict_types=1);

namespace App\Enums;

enum UserLevelEnum: string
{
    case STARTER = 'starter';
    case ELEMENTARY = 'elementary';
    case PRE_INTERMEDIATE = 'pre_intermediate';
    case INTERMEDIATE = 'intermediate';
    case UPPER_INTERMEDIATE = 'upper_intermediate';
    case ADVANCED = 'advanced';

    /**
     * Get the display name for the level
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::STARTER => 'American English File Starter',
            self::ELEMENTARY => 'American English File 1',
            self::PRE_INTERMEDIATE => 'American English File 2',
            self::INTERMEDIATE => 'American English File 3',
            self::UPPER_INTERMEDIATE => 'American English File 4',
            self::ADVANCED => 'American English File 5',
        };
    }

    /**
     * Get the numeric value for sorting
     */
    public function getNumericValue(): int
    {
        return match ($this) {
            self::STARTER => 0,
            self::ELEMENTARY => 1,
            self::PRE_INTERMEDIATE => 2,
            self::INTERMEDIATE => 3,
            self::UPPER_INTERMEDIATE => 4,
            self::ADVANCED => 5,
        };
    }

    /**
     * Get all levels as array for forms
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $level) {
            $options[$level->value] = $level->getDisplayName();
        }

        return $options;
    }

    /**
     * Get the next level
     */
    public function getNextLevel(): ?self
    {
        return match ($this) {
            self::STARTER => self::ELEMENTARY,
            self::ELEMENTARY => self::PRE_INTERMEDIATE,
            self::PRE_INTERMEDIATE => self::INTERMEDIATE,
            self::INTERMEDIATE => self::UPPER_INTERMEDIATE,
            self::UPPER_INTERMEDIATE => self::ADVANCED,
            self::ADVANCED => null,
        };
    }

    /**
     * Get the previous level
     */
    public function getPreviousLevel(): ?self
    {
        return match ($this) {
            self::STARTER => null,
            self::ELEMENTARY => self::STARTER,
            self::PRE_INTERMEDIATE => self::ELEMENTARY,
            self::INTERMEDIATE => self::PRE_INTERMEDIATE,
            self::UPPER_INTERMEDIATE => self::INTERMEDIATE,
            self::ADVANCED => self::UPPER_INTERMEDIATE,
        };
    }
}
