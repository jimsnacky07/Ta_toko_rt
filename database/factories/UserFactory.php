<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('123'), 
            'nama' => $this->faker->name(),
            'no_telp' => '08' . $this->faker->numerify(str_repeat('#', 10)),
            'alamat' => $this->faker->address(),
            'level' => 'user', 
        ];
    }
}
