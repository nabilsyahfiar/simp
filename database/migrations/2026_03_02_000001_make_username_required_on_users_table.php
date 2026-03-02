<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingUsernames = collect(
            DB::table('users')
                ->whereNotNull('username')
                ->where('username', '!=', '')
                ->pluck('username')
        )
            ->map(fn (string $username): string => strtolower($username))
            ->flip();

        DB::table('users')
            ->select('id', 'email', 'username')
            ->orderBy('id')
            ->lazy()
            ->each(function (object $user) use ($existingUsernames): void {
                if (filled($user->username)) {
                    return;
                }

                $emailLocalPart = Str::before((string) $user->email, '@');
                $base = Str::of($emailLocalPart)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9._-]+/', '_')
                    ->trim('._-')
                    ->value();

                if ($base === '') {
                    $base = 'user';
                }

                $candidate = $base;
                $suffix = 1;

                while ($existingUsernames->has(strtolower($candidate))) {
                    $candidate = "{$base}_{$suffix}";
                    $suffix++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $candidate]);

                $existingUsernames->put(strtolower($candidate), true);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->change();
        });
    }
};
