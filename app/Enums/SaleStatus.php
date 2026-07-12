<?php

namespace App\Enums;

enum SaleStatus: string
{
    case COMPLETED = 'completed';
    case VOIDED = 'voided';
    case REFUNDED = 'refunded';
    case PARTIAL_REFUND = 'partial_refund';
}
