<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE_BANKING = 'mobile_banking';
    case BANK_TRANSFER = 'bank_transfer';
}
