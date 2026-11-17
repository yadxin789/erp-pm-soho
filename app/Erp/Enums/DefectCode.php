<?php

namespace App\Erp\Enums;

enum DefectCode: string
{
    case Appearance = 'appearance';
    case Dimension = 'dimension';
    case Electrical = 'electrical';
    case Soldering = 'soldering';
    case Assembly = 'assembly';
    case Material = 'material';
    case Function = 'function';
    case Packaging = 'packaging';
}
