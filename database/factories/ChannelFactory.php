<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->word(),
        ];
    }
}
