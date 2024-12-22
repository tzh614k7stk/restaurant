<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Table;
use App\Models\User;
use DateTime;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //create a random start time between 0900 and 2000
        $start = new DateTime('today ' . fake()->numberBetween(9, 20) . ':00');
        
        //duration options (in hours)
        $durations = [1, 1.5, 2, 2.5, 3];
        $duration = fake()->randomElement($durations);
        
        //calculate end time
        $end = (clone $start)->modify('+' . ($duration * 60) . ' minutes');

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'duration' => $duration,
            'seats' => fake()->numberBetween(1, 8),
            'table_id' => Table::factory(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the reservation is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the reservation is for a future date.
     */
    public function future(int $days = null): static
    {
        $days = $days ?? fake()->numberBetween(1, 30);
        $date = date('Y-m-d', strtotime("+$days days"));
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $date,
            'end_date' => $date,
        ]);
    }

    /**
     * Indicate that the reservation is for a specific date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $date,
            'end_date' => $date,
        ]);
    }

    /**
     * Indicate that the reservation starts at a specific time.
     */
    public function startingAt(string $time): static
    {
        $start = new DateTime("today $time");
        $end = (clone $start)->modify('+' . ($attributes['duration'] * 60) . ' minutes');

        return $this->state(fn (array $attributes) => [
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
        ]);
    }

    /**
     * Indicate that the reservation is for a specific duration.
     */
    public function forDuration(float $duration): static
    {
        return $this->state(function (array $attributes) use ($duration) {
            $start = new DateTime($attributes['start_date'] . ' ' . $attributes['start_time']);
            $end = (clone $start)->modify('+' . ($duration * 60) . ' minutes');

            return [
                'duration' => $duration,
                'end_time' => $end->format('H:i'),
                'end_date' => $end->format('Y-m-d'),
            ];
        });
    }
}
