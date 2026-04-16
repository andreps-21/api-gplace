<?php

namespace App\Enums;

abstract class PaymentStatus
{
    const NotFinished = 0;
    const Authorized = 1;
    const PaymentConfirmed = 2;
    const Denied = 3;
    const Voided = 10;
    const Refunded = 11;
    const Pending = 12;
    const Aborted = 13;
}
