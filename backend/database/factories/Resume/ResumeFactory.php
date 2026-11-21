<?php

namespace Database\Factories\Resume;

use App\Enums\ResumeStatus;
use App\Enums\ResumeTarget;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un CV en brouillon, sans version, non public.
 *
 * @extends Factory<Resume>
 */
class ResumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'CV Développeur Full-Stack',
            'target' => ResumeTarget::General,
            'locale' => 'fr',
            'status' => ResumeStatus::Draft,
            'is_default' => false,
            'is_public' => false,
        ];
    }

    /**
     * CV publié : actif, public, avec une première version courante.
     */
    public function published(bool $isDefault = false): static
    {
        return $this->afterCreating(function (Resume $resume) use ($isDefault): void {
            $version = ResumeVersion::factory()->for($resume)->create(['version_number' => 1]);

            $resume->forceFill([
                'status' => ResumeStatus::Active,
                'current_version_id' => $version->id,
                'is_public' => true,
                'is_default' => $isDefault,
            ])->save();
        });
    }

    /**
     * CV en anglais.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes): array => ['locale' => 'en', 'title' => 'Full-Stack Developer Resume']);
    }
}
