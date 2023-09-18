<?php

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\BaseModel;

class HttpLog extends BaseModel
{
    use SoftDeletes;

    protected $table = 'http_log';

    protected $guarded = [];

    protected $casts = [
        'ext' => 'json',
    ];
}
