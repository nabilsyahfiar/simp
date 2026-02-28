<?php

namespace Tests\Feature;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\User;
use App\Support\ProgressReportVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_report_verify_is_atomic_and_second_attempt_fails(): void
    {
        [$staffA, $staffB, $foreman, $unit] = $this->seedBasicProgressContext();

        $report = ProgressReport::query()->create([
            'unit_id' => $unit->id,
            'foreman_id' => $foreman->id,
            'description' => 'Laporan harian',
            'reported_percent' => 40,
            'status' => 'pending',
            'report_date' => now(),
        ]);

        $first = ProgressReportVerifier::verifyByStaff($report, $staffA->id);
        $second = ProgressReportVerifier::verifyByStaff($report, $staffB->id);

        $this->assertTrue($first);
        $this->assertFalse($second);

        $fresh = $report->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame('verified', $fresh->status);
        $this->assertSame($staffA->id, $fresh->verified_by);
    }

    public function test_verification_does_not_reduce_official_progress(): void
    {
        [$staff, $foreman, $unit] = $this->seedProgressContextWithExistingProgress(80);

        $report = ProgressReport::query()->create([
            'unit_id' => $unit->id,
            'foreman_id' => $foreman->id,
            'description' => 'Laporan terlambat',
            'reported_percent' => 60,
            'status' => 'pending',
            'report_date' => now()->subDay(),
        ]);

        $verified = ProgressReportVerifier::verifyByStaff($report, $staff->id);

        $this->assertTrue($verified);
        $this->assertSame(80, (int) $unit->fresh()->official_progress_percent);
    }

    public function test_auto_unit_code_generation_remains_unique_across_multiple_creates(): void
    {
        $foreman = $this->makeUserWithRole('foreman', 'foreman-seed@example.test');
        $project = $this->makeProject('PRJX');

        HouseUnit::query()->create([
            'project_id' => $project->id,
            'unit_code' => 'PRJX-0001',
            'assigned_foreman_id' => $foreman->id,
            'official_progress_percent' => 0,
        ]);

        $unitTwo = HouseUnit::createWithAutoUnitCode([
            'project_id' => $project->id,
            'assigned_foreman_id' => $foreman->id,
            'official_progress_percent' => 0,
        ]);

        $unitThree = HouseUnit::createWithAutoUnitCode([
            'project_id' => $project->id,
            'assigned_foreman_id' => $foreman->id,
            'official_progress_percent' => 0,
        ]);

        $this->assertSame('PRJX-0002', $unitTwo->unit_code);
        $this->assertSame('PRJX-0003', $unitThree->unit_code);
        $this->assertSame(3, HouseUnit::query()->where('project_id', $project->id)->count());
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: HouseUnit}
     */
    private function seedBasicProgressContext(): array
    {
        $staffA = $this->makeUserWithRole('staff', 'staff-a@example.test');
        $staffB = $this->makeUserWithRole('staff', 'staff-b@example.test');
        $foreman = $this->makeUserWithRole('foreman', 'foreman-a@example.test');
        $project = $this->makeProject('PRG');

        $unit = HouseUnit::query()->create([
            'project_id' => $project->id,
            'unit_code' => 'PRG-0001',
            'assigned_foreman_id' => $foreman->id,
            'official_progress_percent' => 0,
        ]);

        return [$staffA, $staffB, $foreman, $unit];
    }

    /**
     * @return array{0: User, 1: User, 2: HouseUnit}
     */
    private function seedProgressContextWithExistingProgress(int $existingPercent): array
    {
        $staff = $this->makeUserWithRole('staff', 'staff-c@example.test');
        $foreman = $this->makeUserWithRole('foreman', 'foreman-b@example.test');
        $project = $this->makeProject('PRH');

        $unit = HouseUnit::query()->create([
            'project_id' => $project->id,
            'unit_code' => 'PRH-0001',
            'assigned_foreman_id' => $foreman->id,
            'official_progress_percent' => $existingPercent,
        ]);

        return [$staff, $foreman, $unit];
    }

    private function makeUserWithRole(string $roleName, string $email): User
    {
        Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create([
            'email' => $email,
            'username' => str_replace(['@', '.'], '_', $email),
            'is_active' => true,
        ]);

        $user->assignRole($roleName);

        return $user;
    }

    private function makeProject(string $code): Project
    {
        return Project::query()->create([
            'name' => "Project {$code}",
            'code' => $code,
            'location' => 'Bandung',
            'description' => null,
            'status' => 'active',
            'start_date' => now()->toDateString(),
        ]);
    }
}

