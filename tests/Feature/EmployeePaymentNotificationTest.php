<?php

namespace Tests\Feature;

use App\Mail\EmployeePaymentSubmittedMail;
use App\Models\EmployeePayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmployeePaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_payment_submission_notifies_president(): void
    {
        Mail::fake();

        $president = User::factory()->create([
            'role' => User::ROLE_PRESIDENT,
        ]);

        $employee = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $tenant = $employee->tenant()->create([
            'full_name' => $employee->name,
            'type' => Tenant::TYPE_EMPLOYEE,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => 'EMP-001',
            'monthly_rate' => Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE,
            'salary_deduction' => false,
            'phone' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'admission_form_json' => [],
        ]);

        $this->actingAs($employee)->post(route('employee.payments.store'), [
            'billing_month' => '2026-04',
            'amount' => '1800.00',
            'employee_note' => 'Paid over the counter.',
        ])->assertRedirect(route('employee.payments.index'));

        $payment = $tenant->employeePayments()->firstOrFail();

        $this->assertSame(EmployeePayment::STATUS_PENDING, $payment->status);

        Mail::assertQueued(EmployeePaymentSubmittedMail::class, function (EmployeePaymentSubmittedMail $mail) use ($president, $payment) {
            return $mail->hasTo($president->email)
                && $mail->payment->is($payment);
        });
    }
}
