<?php

namespace App\Models\documentBoard;

use Illuminate\Database\Eloquent\Model;

class DocumentBoard extends Model
{

    protected $fillable = ['caption', 'file_path'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('document_boards.ins', '=', auth()->user()->ins);
        });
    }

}
