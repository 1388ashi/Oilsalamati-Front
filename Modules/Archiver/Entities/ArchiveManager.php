<?php

namespace Modules\Archiver\Entities;

use Illuminate\Database\Eloquent\Model;

class ArchiveManager extends Model
{

    protected $fillable = [
        'tablename_source',
        'tablename_archive',
        'row_count',
        'id_start',
        'id_end',
    ];
    protected $connection = 'mysql2';
    protected $table = 'archive_manager';


}
