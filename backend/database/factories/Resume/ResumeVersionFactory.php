<?php

namespace Database\Factories\Resume;

use App\Models\Media\Media;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResumeVersion>
 */
class ResumeVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'media_id' => Media::factory()->privateDocument(),
            'version_number' => 1,
            'change_note' => 'Première version',
        ];
    }
}
