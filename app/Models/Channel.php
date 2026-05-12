<?php

namespace App\Models;

use Database\Factories\ChannelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name'])]
class Channel extends Model
{
    /** @use HasFactory<ChannelFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Channel $channel) {
            if (empty($channel->url_key)) {
                $channel->url_key = Str::random(8);
            }

            if (empty($channel->team_id) && $channel->project_id) {
                $channel->team_id = Project::where('id', $channel->project_id)->value('team_id');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function rotateUrlKey(): void
    {
        $this->forceFill(['url_key' => Str::random(8)])->save();
    }

    public function signedIngestionUrl(): string
    {
        return url()->signedRoute('ingest', [
            'channel' => $this->id,
            'url_key' => $this->url_key,
        ]);
    }
}
