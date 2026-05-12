<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'user_id', 'description'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->team_id) && $event->channel_id) {
                $event->team_id = Channel::where('id', $event->channel_id)->value('team_id');
            }
        });
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function project(): BelongsTo
    {
        return $this->channel->project;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
