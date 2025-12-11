<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\Hash;

class CustomerTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test customer
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'last_login_at' => now(),
        ]);

        // Create test addresses for the customer
        CustomerAddress::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Tech Corp',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'United States',
            'phone' => '+1234567890',
            'is_default' => true,
        ]);

        CustomerAddress::create([
            'user_id' => $user->id,
            'type' => 'billing',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '456 Business Ave',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10002',
            'country' => 'United States',
            'phone' => '+1234567890',
            'is_default' => true,
        ]);

        echo "Test customer created:\n";
        echo "Email: customer@example.com\n";
        echo "Password: password123\n";
    }
}