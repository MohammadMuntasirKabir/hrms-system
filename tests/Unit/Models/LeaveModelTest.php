<?php

use App\Models\Leave;
use Carbon\CarbonImmutable;

it('has expected statuses and types', function () {
    expect(Leave::STATUSES)->toBe(['pending', 'approved', 'rejected', 'cancelled']);
    expect(Leave::TYPES)->toContain('annual');
    expect(Leave::TYPES)->toContain('sick');
});

it('computes pending and approved flags', function () {
    $pending = Leave::factory()->pending()->make();
    $approved = Leave::factory()->approved()->make();

    expect($pending->isPending)->toBeTrue();
    expect($pending->isApproved)->toBeFalse();
    expect($approved->isApproved)->toBeTrue();
    expect($approved->isRejected)->toBeFalse();
});

it('casts dates and total days', function () {
    $leave = Leave::factory()->create([
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-05',
        'total_days' => 5,
    ]);

    expect($leave->start_date)->toBeInstanceOf(CarbonImmutable::class);
    expect($leave->total_days)->toBeInt();
});
