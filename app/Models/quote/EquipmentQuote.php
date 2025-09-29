<?php

namespace App\Models\quote;

use App\Models\quote\Quote;
use Illuminate\Database\Eloquent\Model;

class EquipmentQuote extends Model
{
    protected $table = 'quote_equipment';

    protected $fillable = ['unique_id','equipment_tid', 'equip_serial','quote_id','location','make_type','capacity','row_index_id','fault','item_id'];

    public $timestamps = false;

    /**
     * Relations
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
