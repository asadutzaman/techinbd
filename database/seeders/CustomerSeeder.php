<?php

namespace Database\Seeders;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo customers for trying the shop: php artisan db:seed --class=CustomerSeeder
 *
 * Each signs in with the password 12345678. Their emails are plus-addresses of the shop's sending address
 * (MAIL_FROM_ADDRESS): you@gmail.com gives you+rahim@gmail.com, which Gmail delivers to you@gmail.com,
 * so every email these customers get lands in the shop's own inbox. Not part of DatabaseSeeder, so the
 * known password never reaches a production seed. Safe to run again: it updates the same accounts.
 */
class CustomerSeeder extends Seeder
{
    public const PASSWORD = '12345678';

    /**
     * [name, mailbox tag, mobile, address line, area, city, district, postcode]
     */
    private const CUSTOMERS = [
        ['Rahim Uddin', 'rahim', '01711000001', 'House 12, Road 5', 'Dhanmondi', 'Dhaka', 'Dhaka', '1205'],
        ['Nusrat Jahan', 'nusrat', '01811000002', 'Flat 4B, 22 Kemal Ataturk Avenue', 'Banani', 'Dhaka', 'Dhaka', '1213'],
        ['Tanvir Ahmed', 'tanvir', '01911000003', '45 CDA Avenue', 'GEC Circle', 'Chattogram', 'Chattogram', '4000'],
        ['Farhana Akter', 'farhana', '01611000004', '7 Zindabazar Road', 'Zindabazar', 'Sylhet', 'Sylhet', '3100'],
        ['Karim Hasan', 'karim', '01511000005', '18 Shaheb Bazar Road', 'Boalia', 'Rajshahi', 'Rajshahi', '6100'],
    ];

    public function run(): void
    {
        [$mailbox, $domain] = explode('@', (string) config('mail.from.address'), 2) + [1 => 'example.com'];
        $mailbox = explode('+', $mailbox)[0];

        $rows = [];
        foreach (self::CUSTOMERS as [$name, $tag, $mobile, $line1, $area, $city, $district, $postcode]) {
            $email = "{$mailbox}+{$tag}@{$domain}";

            // The model's cast hashes the password
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => self::PASSWORD,
                'phone' => $mobile,
            ]);

            [$first, $last] = explode(' ', $name, 2);
            CustomerAddress::updateOrCreate(
                ['user_id' => $user->id, 'type' => 'shipping', 'is_default' => true],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'address_line_1' => $line1,
                    'address_line_2' => $area,
                    'city' => $city,
                    'state' => $district,
                    'postal_code' => $postcode,
                    'country' => 'Bangladesh',
                    'phone' => $mobile,
                ]
            );

            $rows[] = [$name, $email, self::PASSWORD];
        }

        $this->command?->table(['Customer', 'Email', 'Password'], $rows);
    }
}
