<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PdsRepository
{
    private const CACHE_TTL = 300; // 5 minutes
    private const MAX_ROWS_PER_TABLE = 45;

    /**
     * Get all PDS data for a user with caching.
     */
    public function getAllPdsData(int $userId): array
    {
        $cacheKey = "pds:all:{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return [
                'personal' => $this->getPersonalInfo($userId),
                'address' => $this->getAddress($userId),
                'contact' => $this->getContactInfo($userId),
                'idInfo' => $this->getIdInfo($userId),
                'family' => $this->getFamilyMembers($userId),
                'education' => $this->getEducation($userId),
                'eligibilities' => $this->getEligibilities($userId),
                'workExperiences' => $this->getWorkExperiences($userId),
                'voluntaryWorks' => $this->getVoluntaryWorks($userId),
                'training' => $this->getTraining($userId),
                'otherInfo' => $this->getOtherInfo($userId),
                'references' => $this->getReferences($userId),
                'declaration' => $this->getDeclaration($userId),
                'remarks' => $this->getRemarks($userId),
                'signatureFiles' => $this->getSignatureFiles($userId),
            ];
        });
    }

    public function clearCache(int $userId): void
    {
        Cache::forget("pds:all:{$userId}");
        Cache::forget("pds:family:{$userId}");
        Cache::forget("pds:education:{$userId}");
        Cache::forget("pds:training:{$userId}");
    }

    public function getPersonalInfo(int $userId): ?object
    {
        return DB::table('pds_personal_infos')->where('user_id', $userId)->first();
    }

    public function getAddress(int $userId): ?object
    {
        return DB::table('pds_addresses')->where('user_id', $userId)->first();
    }

    public function getContactInfo(int $userId): ?object
    {
        return DB::table('pds_contact_infos')->where('user_id', $userId)->first();
    }

    public function getIdInfo(int $userId): ?object
    {
        return DB::table('pds_id_infos')->where('user_id', $userId)->first();
    }

    public function getFamilyMembers(int $userId): Collection
    {
        $cacheKey = "pds:family:{$userId}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return DB::table('pds_family_members')
                ->where('user_id', $userId)
                ->get();
        });
    }

    public function getEducation(int $userId): Collection
    {
        $cacheKey = "pds:education:{$userId}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return DB::table('pds_education_records')
                ->where('user_id', $userId)
                ->orderBy('id')
                ->get();
        });
    }

    public function getEligibilities(int $userId): Collection
    {
        return DB::table('pds_eligibilities')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNotNull('eligibility')
                      ->where('eligibility', '!=', '')
                      ->whereNotIn('eligibility', ['NA', 'N/A', 'NONE']);
            })
            ->get();
    }

    public function getWorkExperiences(int $userId): Collection
    {
        return DB::table('pds_work_experiences')
            ->where('user_id', $userId)
            ->get();
    }

    public function getVoluntaryWorks(int $userId): Collection
    {
        return DB::table('pds_voluntary_work')
            ->where('user_id', $userId)
            ->get();
    }

    public function getTraining(int $userId): array
    {
        $cacheKey = "pds:training:{$userId}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $allTraining = DB::table('pds_training_programs')
                ->where('user_id', $userId)
                ->orderBy('id')
                ->get();

            return [
                'main' => $allTraining->filter(fn($row) => ($row->source ?? 'main') === 'main')->values(),
                'added' => $allTraining->filter(fn($row) => ($row->source ?? '') === 'added')->values(),
            ];
        });
    }

    public function getOtherInfo(int $userId): Collection
    {
        return DB::table('pds_other_info')
            ->where('user_id', $userId)
            ->get();
    }

    public function getReferences(int $userId): Collection
    {
        return DB::table('pds_references')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->limit(7)
            ->get();
    }

    public function getDeclaration(int $userId): ?object
    {
        return DB::table('pds_declarations')
            ->where('user_id', $userId)
            ->first();
    }

    public function getRemarks(int $userId): Collection
    {
        return DB::table('pds_form5_remarks')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    public function getSignatureFiles(int $userId): ?object
    {
        return DB::table('pds_signature_files')
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Build extra education tables with pagination support.
     * Uses source column to distinguish main vs added rows.
     */
    public function buildExtraEducationTables(Collection $education): Collection
    {
        if ($education->isEmpty()) {
            return collect();
        }

        $extraRows = $education->filter(fn ($row) => ($row->source ?? 'main') === 'added')->values();

        if ($extraRows->isEmpty()) {
            return collect();
        }

        $extraTables = collect();
        $extraTableCount = ceil($extraRows->count() / 5);

        for ($i = 0; $i < $extraTableCount; $i++) {
            $tableRows = $extraRows->slice($i * 5, 5)->values();
            $extraTables->push($tableRows->map(fn ($row) => [
                'level' => $row->level,
                'school_name' => $row->school_name,
                'degree_course' => $row->degree_course,
                'basic_education' => $row->degree_course,
                'from' => $row->from,
                'to' => $row->to,
                'highest_level' => $row->highest_level,
                'year_graduated' => $row->year_graduated,
                'academic_honors' => $row->academic_honors,
                'scholarship_acadhonors' => $row->academic_honors,
            ]));
        }

        return $extraTables;
    }

    /**
     * Build extra training tables with optimized row allocation.
     */
    public function buildExtraTrainingTables(Collection $addedTraining): Collection
    {
        if ($addedTraining->isEmpty()) {
            return collect();
        }

        // Group into tables of MAX_ROWS_PER_TABLE
        $tables = collect();
        $tableCount = ceil($addedTraining->count() / self::MAX_ROWS_PER_TABLE);

        for ($i = 0; $i < $tableCount; $i++) {
            $tables->push($addedTraining->slice($i * self::MAX_ROWS_PER_TABLE, self::MAX_ROWS_PER_TABLE)->values());
        }

        return $tables;
    }

}
