<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PdsPersistenceService
{
    private function rowHasData(array $row): bool
    {
        return collect($row)->some(fn ($v) => strlen(trim((string) $v)) > 0);
    }

    public function persistPersonalInfo(int $userId, array $d): void
    {
        DB::table('pds_personal_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'surname'            => $d['surname'] ?? null,
                'firstname'          => $d['firstname'] ?? null,
                'middlename'         => $d['middlename'] ?? null,
                'name_extension'     => $d['employee_name_extension'] ?? null,
                'date_of_birth'      => $d['date_of_birth'] ?? null,
                'place_of_birth'     => $d['place_of_birth'] ?? null,
                'sex'                => collect($d['sex'] ?? [])->first(),
                'civil_status'       => collect($d['civilstatus'] ?? [])->first(),
                'height'             => $d['height'] ?? null,
                'weight'             => $d['weight'] ?? null,
                'blood_type'         => $d['blood_type'] ?? null,
                'umid_no'            => $d['umid_id_no'] ?? null,
                'country'            => $d['country'] ?? null,
                'pagibig_no'         => $d['pagibig_id_no'] ?? null,
                'philhealth_no'      => $d['philhealth_no'] ?? null,
                'philsys_no'         => $d['philsys_no'] ?? null,
                'tin_no'             => $d['tin_no'] ?? null,
                'agency_employee_no' => $d['agency_employee_no'] ?? null,
                'citizenship'        => collect($d['citizenship'] ?? [])->first(),
            ]
        );
    }

    public function persistAddress(int $userId, array $d): void
    {
        DB::table('pds_addresses')->updateOrInsert(
            ['user_id' => $userId],
            [
                'present_house_block_lot'       => $d['house_block_lot'] ?? null,
                'present_street'                => $d['street'] ?? null,
                'present_subdivision_village'   => $d['subdivision_village'] ?? null,
                'present_barangay'              => $d['baranggay'] ?? null,
                'present_city_municipality'     => $d['city_municipality'] ?? null,
                'present_province'              => $d['province'] ?? null,
                'present_zip_code'              => $d['zip_code'] ?? null,
                'permanent_house_block_lot'     => $d['permanent_house_block_lot'] ?? null,
                'permanent_street'              => $d['permanent_street'] ?? null,
                'permanent_subdivision_village' => $d['permanent_subdivision_village'] ?? null,
                'permanent_barangay'            => $d['permanent_baranggay'] ?? null,
                'permanent_city_municipality'   => $d['permanent_city_municipality'] ?? null,
                'permanent_province'            => $d['permanent_province'] ?? null,
            ]
        );
    }

    public function persistContactInfo(int $userId, array $d): void
    {
        DB::table('pds_contact_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'telephone_no'  => $d['telephone_no'] ?? null,
                'mobile_no'     => $d['mobile_no'] ?? null,
                'email_address' => $d['email_address'] ?? null,
            ]
        );
    }

    public function persistIdInfo(int $userId, array $d): void
    {
        DB::table('pds_id_infos')->updateOrInsert(
            ['user_id' => $userId],
            [
                'gov_id'              => $d['gov_id'] ?? null,
                'passport_licence_id' => $d['licence_passport_id'] ?? null,
                'date_place_issuance' => $d['id_issue_date_place'] ?? null,
            ]
        );
    }

    public function persistFamilyMembers(int $userId, array $d): void
    {
        DB::table('pds_family_members')->where('user_id', $userId)->delete();

        $spouse = [
            'type'             => 'spouse',
            'firstname'        => $d['spouse_firstname'] ?? null,
            'middlename'       => $d['spouse_middlename'] ?? null,
            'surname'          => $d['spouse_surname'] ?? null,
            'name_extension'   => $d['spouse_name_extension'] ?? null,
            'occupation'       => $d['spouse_occupation'] ?? null,
            'employer'         => $d['spouse_employer_business_name'] ?? null,
            'business_address' => $d['spouse_business_address'] ?? null,
            'telephone_no'     => $d['spouse_telephone_no'] ?? null,
        ];
        if ($this->rowHasData(array_diff_key($spouse, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($spouse, ['user_id' => $userId]));
        }

        $childNames = collect($d['children_familybg'] ?? []);
        $childDobs  = collect($d['children_dateofbirth_familybg'] ?? []);
        $children = $childNames->map(function ($name, $i) use ($childDobs, $userId) {
            return [
                'user_id'       => $userId,
                'type'          => 'child',
                'firstname'     => $name,
                'date_of_birth' => $childDobs->get($i),
            ];
        })->filter(fn ($row) => strlen(trim((string) ($row['firstname'] ?? ''))) > 0);

        $hasRealChild = $children->contains(function ($row) {
            $name = strtoupper(trim((string) $row['firstname']));
            return $name !== '' && $name !== 'NA';
        });

        if ($hasRealChild) {
            $children = $children->filter(function ($row) {
                $name = strtoupper(trim((string) $row['firstname']));
                return $name !== '' && $name !== 'NA';
            });
        } elseif ($children->isNotEmpty()) {
            $children = collect([$children->first()]);
        }

        if ($children->isNotEmpty()) {
            DB::table('pds_family_members')->insert($children->all());
        }

        $father = [
            'type'           => 'father',
            'firstname'      => $d['father_firstname'] ?? null,
            'middlename'     => $d['father_middlename'] ?? null,
            'surname'        => $d['father_surname'] ?? null,
            'name_extension' => $d['father_name_extension'] ?? null,
        ];
        if ($this->rowHasData(array_diff_key($father, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($father, ['user_id' => $userId]));
        }

        $mother = [
            'type'       => 'mother',
            'firstname'  => $d['mother_firstname'] ?? null,
            'middlename' => $d['mother_middlename'] ?? null,
            'surname'    => $d['mother_surname'] ?? null,
        ];
        if ($this->rowHasData(array_diff_key($mother, ['type' => true]))) {
            DB::table('pds_family_members')->insert(array_merge($mother, ['user_id' => $userId]));
        }
    }

    public function persistForm1(int $userId, array $d): void
    {
        $this->persistPersonalInfo($userId, $d);
        $this->persistAddress($userId, $d);
        $this->persistContactInfo($userId, $d);
        $this->persistIdInfo($userId, $d);
        $this->persistFamilyMembers($userId, $d);
    }
}
