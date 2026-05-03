<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;
    private int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        $levels = DB::table('level')->pluck('levelid', 'levelname')->all();
        $levels = array_change_key_case($levels, CASE_LOWER);

        $roles = DB::table('role')->pluck('roleid', 'rolename')->all();
        $roles = array_change_key_case($roles, CASE_LOWER);

        foreach ($rows as $row) {
            $username = trim((string) ($row['username'] ?? ''));
            if (empty($username)) {
                $this->skippedCount++;
                continue;
            }

            $exists = DB::table('user')->where('username', $username)->exists();
            if ($exists) {
                $this->skippedCount++;
                continue;
            }

            $levelname = strtolower(trim((string) ($row['level'] ?? '')));
            $levelid = $levels[$levelname] ?? 2;

            $rolename = strtolower(trim((string) ($row['role'] ?? '')));
            $roleid = $roles[$rolename] ?? null;

            DB::transaction(function () use ($row, $username, $levelid, $roleid) {
                $userId = DB::table('user')->insertGetId([
                    'username' => $username,
                    'password' => Hash::make($username),
                    'levelid' => $levelid,
                ]);

                $isFamilyLevel = in_array((int) $levelid, [2, 4], true);

                if ($isFamilyLevel) {
                    $gender = strtolower(trim((string) ($row['gender'] ?? '')));
                    if (!in_array($gender, ['male', 'female'])) {
                        $gender = 'male';
                    }

                    $picture = $gender === 'male'
                        ? '/images/avatar-male.svg'
                        : '/images/avatar-female.svg';
                    $importedPicture = trim((string) ($row['profile_picture'] ?? ''));
                    if ($importedPicture !== '' && $importedPicture !== '-') {
                        $picture = $importedPicture;
                    }

                    $birthdate = null;
                    if (!empty($row['birthdate']) && $row['birthdate'] !== '-') {
                        try {
                            $birthdate = Carbon::parse($row['birthdate'])->toDateString();
                        } catch (\Throwable $e) {
                            $birthdate = null;
                        }
                    }

                    $lifeStatusRaw = strtolower(trim((string) ($row['life_status'] ?? 'alive')));
                    $lifeStatus = in_array($lifeStatusRaw, ['deceased', 'dead'], true) ? 'deceased' : 'alive';

                    DB::table('family_member')->insert([
                        'name' => $row['full_name'] ?? $username,
                        'email' => ($row['email'] ?? '') === '-' ? '' : ($row['email'] ?? ''),
                        'phonenumber' => ($row['phone'] ?? '') === '-' ? '' : ($row['phone'] ?? ''),
                        'gender' => $gender,
                        'birthdate' => $birthdate,
                        'birthplace' => ($row['birthplace'] ?? '') === '-' ? null : ($row['birthplace'] ?? null),
                        'address' => ($row['address'] ?? '') === '-' ? null : ($row['address'] ?? null),
                        'job' => ($row['job'] ?? '') === '-' ? null : ($row['job'] ?? null),
                        'education_status' => ($row['education_status'] ?? '') === '-' ? null : ($row['education_status'] ?? null),
                        'life_status' => $lifeStatus,
                        'marital_status' => ($row['marital_status'] ?? '') === '-' ? 'single' : ($row['marital_status'] ?? 'single'),
                        'deaddate' => $lifeStatus === 'deceased' ? now()->toDateString() : null,
                        'picture' => $picture,
                        'userid' => $userId,
                    ]);
                } else {
                    DB::table('employer')->insert([
                        'name' => $row['full_name'] ?? $username,
                        'email' => ($row['email'] ?? '') === '-' ? '' : ($row['email'] ?? ''),
                        'phonenumber' => ($row['phone'] ?? '') === '-' ? '' : ($row['phone'] ?? ''),
                        'roleid' => $roleid ?: 2,
                        'userid' => $userId,
                    ]);
                }
            });

            $this->importedCount++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
