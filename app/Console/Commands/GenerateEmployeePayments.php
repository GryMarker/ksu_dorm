<?php

namespace App\Console\Commands;

use App\Models\EmployeePayment;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateEmployeePayments extends Command
{
    protected $signature = 'payments:generate {--month=}';

    protected $description = 'Create monthly employee payment records for approved employees';

    public function handle(): int
    {
        $monthOption = $this->option('month');
        $billingMonth = $monthOption
            ? Carbon::createFromFormat('Y-m', $monthOption)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $tenants = Tenant::query()
            ->where('type', Tenant::TYPE_EMPLOYEE)
            ->where('onboarding_status', Tenant::STATUS_APPROVED)
            ->with('employeePayments')
            ->get();

        $createdCount = 0;

        foreach ($tenants as $tenant) {
            $exists = $tenant->employeePayments
                ->contains(fn (EmployeePayment $payment) => $payment->billing_month->equalTo($billingMonth));

            if ($exists) {
                continue;
            }

            $tenant->employeePayments()->create([
                'billing_month' => $billingMonth,
                'amount' => $tenant->monthly_rate ?? Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE,
                'salary_deduction' => (bool) $tenant->salary_deduction,
                'status' => EmployeePayment::STATUS_PENDING,
            ]);

            $createdCount++;
        }

        $this->info("Generated {$createdCount} payment record(s) for {$billingMonth->format('F Y')}.");

        return self::SUCCESS;
    }
}

