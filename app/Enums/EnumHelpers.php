<?php

namespace App\Enums;

trait EnumHelpers
{
    /**
     * Convert a value like "bodyweight_only" into "Bodyweight Only".
     */
    public function label(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->value));
    }

    /**
     * Return an array of ['label' => ..., 'value' => ...] for selects.
     */
    public static function options(): array
    {
        return array_map(
            fn(self $case) => [
                'label' => $case->label(),
                'value' => $case->value,
            ],
            self::cases()
        );
    }

    /**
     * Just return all pure values: ['barbell','machine','freemotion',...]
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * Return a map of value => label.
     */
    public static function map(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->value] = $case->label();
        }
        return $map;
    }
}
