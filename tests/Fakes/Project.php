<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'counter'];
}
