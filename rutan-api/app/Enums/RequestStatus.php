<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED_KAUR = 'approved_kaur';
    case APPROVED_KASI = 'approved_kasi';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
}