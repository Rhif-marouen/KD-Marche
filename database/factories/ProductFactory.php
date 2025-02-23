<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à cette factory.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Définir l'état par défaut du modèle.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 1, 100),
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => \App\Models\Category::inRandomOrder()->first()->id,
            'image_url' => $this->faker->imageUrl,
            // Dans ProductFactory.php
            'quality' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E'])
             ];
    }
}
