<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    private bool $onlyFamilyUsers;

    public function __construct(bool $onlyFamilyUsers = false)
    {
        $this->onlyFamilyUsers = $onlyFamilyUsers;
    }

    public function collection()
    {
        $query = DB::table('user as u')
            ->leftJoin('level as l', 'l.levelid', '=', 'u.levelid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->select(
                'u.username',
                'l.levelname',
                'r.rolename',
                DB::raw("COALESCE(e.name, fm.name) as fullname"),
                DB::raw("COALESCE(e.email, fm.email) as email"),
                DB::raw("COALESCE(e.phonenumber, fm.phonenumber) as phone"),
                'fm.picture',
                'fm.gender',
                'fm.address',
                'fm.marital_status',
                'fm.birthdate',
                'fm.birthplace',
                'fm.job',
                'fm.education_status',
                'fm.life_status'
            );

        if ($this->onlyFamilyUsers) {
            $query->where('u.levelid', 2);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Username',
            'Full Name',
            'Level',
            'Role',
            'Email',
            'Phone',
            'Profile Picture',
            'Gender',
            'Address',
            'Marital Status',
            'Birthdate',
            'Birthplace',
            'Job',
            'Education Status',
            'Life Status',
        ];
    }

    public function map($user): array
    {
        return [
            $user->username,
            $user->fullname,
            $user->levelname,
            $user->rolename ?: '-',
            $user->email,
            $user->phone,
            $user->picture ?: '-',
            $user->gender ?: '-',
            $user->address ?: '-',
            $user->marital_status ?: '-',
            $user->birthdate ?: '-',
            $user->birthplace ?: '-',
            $user->job ?: '-',
            $user->education_status ?: '-',
            $user->life_status ?: '-',
        ];
    }
}
