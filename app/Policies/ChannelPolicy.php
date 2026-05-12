<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Channel $channel): bool
    {
        return $user->belongsToTeam($channel->team);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Channel $channel): bool
    {
        return $user->belongsToTeam($channel->team);
    }

    public function delete(User $user, Channel $channel): bool
    {
        return $user->belongsToTeam($channel->team);
    }
}
