<?php

namespace App\Enums;

enum ExportDetailLevel: string
{
    case Summary = 'summary';
    case Standard = 'standard';
    case Detailed = 'detailed';
    case Executive = 'executive';

    public function label(): string
    {
        return match ($this) {
            self::Summary => 'Summary',
            self::Standard => 'Standard',
            self::Detailed => 'Detailed audit',
            self::Executive => 'Executive summary',
        };
    }

    public static function fromQuery(?string $value): self
    {
        $v = strtolower(trim((string) $value));

        foreach (self::cases() as $case) {
            if ($case->value === $v) {
                return $case;
            }
        }

        return self::Standard;
    }
}
