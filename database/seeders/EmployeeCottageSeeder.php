<?php

namespace Database\Seeders;

use App\Models\EmployeeCottage;
use Illuminate\Database\Seeder;

class EmployeeCottageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 12) as $number) {
            $code = 'Cottage-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            EmployeeCottage::updateOrCreate(
                ['code' => $code],
                [
                    'building' => 'Employee Village',
                    'wing' => 'Family Lane',
                    'status' => EmployeeCottage::STATUS_AVAILABLE,
                    'tenant_id' => null,
                    'requested_tenant_id' => null,
                    'requested_at' => null,
                    'family_members' => null,
                ]
            );
        }
    }
}
