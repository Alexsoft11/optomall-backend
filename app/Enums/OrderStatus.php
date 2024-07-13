<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Ver_underVerification = 'under_verification';

    case Ver_verified = 'verified';

    case Ver_failedVerification = 'failed_verification';

    case Processing = 'processing';

    case Shipped = 'shipped';

    case Delivered = 'delivered';

    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Ver_underVerification => 'Under Verification',
            self::Ver_verified => 'Verified',
            self::Ver_failedVerification => 'Failed Verification',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Processing => 'warning',
            self::Shipped, self::Delivered => 'success',
            self::Cancelled => 'danger',
            self::Ver_underVerification => 'warning',
            self::Ver_verified => 'success',
            self::Ver_failedVerification => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Processing => 'heroicon-m-arrow-path',
            self::Shipped => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-check-badge',
            self::Cancelled => 'heroicon-m-x-circle',
            self::Ver_underVerification => 'heroicon-m-check-circle',
            self::Ver_verified => 'heroicon-m-check-circle',
            self::Ver_failedVerification => 'heroicon-m-x-circle',
        };
    }
}
