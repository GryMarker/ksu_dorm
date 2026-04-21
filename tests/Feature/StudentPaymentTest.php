<?php

namespace Tests\Feature;

use App\Mail\StudentPaymentSubmittedMail;
use App\Models\StudentPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_student_can_submit_payment_with_custom_amount(): void
    {
        Mail::fake();

        [$user, $tenant] = $this->createApprovedStudent();
        $dormMaster = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $this->actingAs($user)->post(route('tenant.payments.store'), [
            'billing_month' => '2026-04',
            'amount' => '725.50',
            'student_note' => 'Includes prior balance.',
        ])->assertRedirect(route('tenant.payments.index'));

        $payment = $tenant->studentPayments()->firstOrFail();

        $this->assertSame('2026-04-01', $payment->billing_month->toDateString());
        $this->assertSame('725.50', $payment->amount);
        $this->assertSame(StudentPayment::STATUS_PENDING, $payment->status);
        $this->assertSame('Includes prior balance.', $payment->student_note);

        Mail::assertQueued(StudentPaymentSubmittedMail::class, function (StudentPaymentSubmittedMail $mail) use ($dormMaster, $payment) {
            return $mail->hasTo($dormMaster->email)
                && $mail->payment->is($payment);
        });
    }

    public function test_dorm_master_can_approve_student_payment_and_adjust_amount(): void
    {
        [, $tenant] = $this->createApprovedStudent();
        $dormMaster = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $payment = $tenant->studentPayments()->create([
            'billing_month' => '2026-04-01',
            'amount' => '500.00',
            'status' => StudentPayment::STATUS_PENDING,
            'student_note' => 'April dorm fee.',
        ]);

        $this->actingAs($dormMaster)->patch(route('admin.payments.approve', $payment), [
            'amount' => '650.00',
            'review_note' => 'Adjusted after review.',
        ])->assertRedirect(route('admin.payments.index'));

        $payment->refresh();

        $this->assertSame(StudentPayment::STATUS_APPROVED, $payment->status);
        $this->assertSame('650.00', $payment->amount);
        $this->assertSame('Adjusted after review.', $payment->review_note);
        $this->assertSame($dormMaster->id, $payment->reviewed_by);
        $this->assertNotNull($payment->reviewed_at);
    }

    private function createApprovedStudent(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);

        $tenant = $user->tenant()->create([
            'full_name' => $user->name,
            'type' => Tenant::TYPE_STUDENT,
            'onboarding_status' => Tenant::STATUS_APPROVED,
            'university_id_no' => '12-34567',
            'phone' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'admission_form_json' => [],
        ]);

        return [$user, $tenant];
    }
}
