<?php

namespace App\Models\calendar;

use App\Models\Access\User\User;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{

    protected $primaryKey = 'event_number';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'event_number',
        'title',
        'category',
        'description',
        'location',
        'organizer',
        'start',
        'end',
        'color',
    ];

    protected $appends = ['organizer_name' ,'participant_names', 'participants'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {

            $builder->where('ins', '=', auth()->user()->ins);
        });
    }


    // Define relationship with organizer (User model)
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer');
    }

    // Define relationship with participants (many-to-many with User model)
    public function eventParticipants()
    {
        return $this->belongsToMany(User::class, 'calendar_event_participants', 'event_number', 'user_id')
            ->withTimestamps();
    }

    public function getParticipantNamesAttribute()
    {

        $dataArray = $this->eventParticipants()->orderBy('first_name')->get()->map(function ($participant) {

            return $participant->first_name . ' ' . $participant->last_name;
        })->toArray();

        return implode(', ', $dataArray);
    }

    public function getOrganizerNameAttribute()
    {

        return $this->organizer()->first()->first_name . ' ' . $this->organizer()->first()->last_name;
    }

    public function getParticipantsAttribute(){

        return $this->eventParticipants()->pluck('user_id')->toArray();
    }
}
