<?php

namespace App\Models\project;

use App\Models\quote\Quote;
use Illuminate\Database\Eloquent\Model;

class ProjectQuote extends Model
{
    protected $table = 'project_quotes';

    protected $fillable = ['project_id', 'quote_id'];

    public $timestamps = false;

    /**
     * Relations
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
