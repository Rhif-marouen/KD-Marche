<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Création des utilisateurs
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('UserPassword123!'),
            'is_active' => true,
        ]);

        // Création des catégories
        $categories = [
            ['name' => 'A'],
            ['name' => 'B'],
            ['name' => 'C'],
            ['name' => 'D'],
            ['name' => 'E']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Création des produits
        Product::create([
            'name' => 'Pack 10kg Farine',
            'category_id' => 2, // Assurez-vous que la catégorie B existe
            'price' => 29.99,
            'stock' => 50,
            'description' => 'Farine de blé premium',
            'image_url' => 'https://example.com/farine.jpg'
        ]);

        // Création d'abonnement
        Subscription::create([
            'user_id' => $testUser->id,
            'status' => 'active',
            'amount' => 10.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'payment_method' => 'stripe'
        ]);

        // Création de données factices supplémentaires
        User::factory(10)->create();
        Product::factory(20)->create();
    }
}