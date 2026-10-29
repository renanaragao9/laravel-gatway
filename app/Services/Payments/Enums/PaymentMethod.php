<?php

namespace App\Services\Payments\Enums;

enum PaymentMethod: string
{
    case Pix = 'pix';
    case CreditCard = 'card';
    case Boleto = 'boleto';
}
