<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PdsDraftDataService
{
    private const BASE_LEVELS = [
        'elementary'      => 'ELEMENTARY',
        'secondary'       => 'SECONDARY',
        'vocational'      => 'VOCATIONAL / TRADE COURSE',
        'college'         => 'COLLEGE',
        'graduate_studies'=> 'GRADUATE STUDIES',
    ];

    /**
     * Build extra education tables (education_1, education_2, …) from draft data.
     * Returns a Collection where each item is a Collection of row arrays for one table.
     */
    public function buildExtraEduTables(array $draftData): Collection
    {
        $tables = collect();
        $dynKeys = $this->dynKeys($draftData, '/^education_\d+$/');

        foreach ($dynKeys as $dynKey) {
            $table = $draftData[$dynKey];
            if (!is_array($table)) continue;

            $tableRows = collect();
            foreach (self::BASE_LEVELS as $key => $label) {
                $tableRows->push($this->mapEduRow($label, $table[$key] ?? []));
            }

            $hasData = $tableRows->some(function ($r) {
                return collect(array_diff_key($r, ['level' => true]))
                    ->some(fn ($v) => trim((string) ($v ?? '')) !== '');
            });

            if ($hasData) {
                $tables->push($tableRows);
            }
        }

        return $tables;
    }

    /**
     * Parse the primary education rows from draft data into the standard shape.
     */
    public function parseBaseEducation(array $draftData): Collection
    {
        $education = collect();

        foreach (self::BASE_LEVELS as $key => $label) {
            if (!empty($draftData['education'][$key])) {
                $education->push($this->mapEduRow($label, $draftData['education'][$key]));
            }
        }

        $extras = collect($draftData['education_extra_level'] ?? [])
            ->map(function ($level, $i) use ($draftData) {
                return [
                    'level'           => $level ?? null,
                    'school_name'     => $draftData['education_extra_school_name'][$i] ?? null,
                    'degree_course'   => $draftData['education_extra_basic_education'][$i] ?? null,
                    'from'            => $draftData['education_extra_from'][$i] ?? null,
                    'to'              => $draftData['education_extra_to'][$i] ?? null,
                    'highest_level'   => $draftData['education_extra_highest_level'][$i] ?? null,
                    'year_graduated'  => $draftData['education_extra_year_graduated'][$i] ?? null,
                    'academic_honors' => $draftData['education_extra_scholarship_acadhonors'][$i] ?? null,
                ];
            })
            ->filter(function ($row) {
                return collect($row)->some(function ($val) {
                    $v = trim((string) ($val ?? ''));
                    return $v !== '' && !in_array(strtoupper($v), ['NA', 'N/A', 'NONE'], true);
                });
            });

        return $education->concat($extras)->values();
    }

    /**
     * Build the primary training rows from draft data.
     * Returns a Collection of stdClass objects matching pds_training_programs shape.
     */
    public function parseTrainingFromDraft(array $draftData): Collection
    {
        $titles    = $draftData['learning_title_of_ld'] ?? [];
        $fromDates = $draftData['learning_from'] ?? [];
        $toDates   = $draftData['learning_to'] ?? [];
        $hours     = $draftData['learning_hours'] ?? [];
        $types     = $draftData['learning_type_of_ld'] ?? [];
        $conducted = $draftData['learning_conducted_sponsored_by'] ?? [];

        $rows = collect();
        foreach ($titles as $i => $title) {
            $hasData = !empty($title) || !empty($fromDates[$i]) || !empty($toDates[$i])
                || !empty($hours[$i]) || !empty($types[$i]) || !empty($conducted[$i]);
            if ($hasData) {
                $rows->push((object) [
                    'title'        => $title,
                    'from'         => $fromDates[$i] ?? null,
                    'to'           => $toDates[$i] ?? null,
                    'hours'        => $hours[$i] ?? null,
                    'type_of_ld'   => $types[$i] ?? null,
                    'conducted_by' => $conducted[$i] ?? null,
                ]);
            }
        }

        return $rows;
    }

    /**
     * Build extra training tables (learning_1, learning_2, …) from draft data.
     * Returns a Collection keyed by the dynamic key (e.g. "learning_1").
     */
    public function buildExtraTrainingTables(array $draftData): Collection
    {
        $tables = collect();
        $dynKeys = $this->dynKeys($draftData, '/^learning_\d+$/');

        foreach ($dynKeys as $dynKey) {
            $table = $draftData[$dynKey];
            if (!is_array($table)) continue;

            $tableRows = collect();
            $rowCount = count($table['title_of_ld'] ?? []);

            for ($i = 0; $i < $rowCount; $i++) {
                $rowData = (object) [
                    'title'        => $table['title_of_ld'][$i] ?? null,
                    'from'         => $table['from'][$i] ?? null,
                    'to'           => $table['to'][$i] ?? null,
                    'hours'        => $table['hours'][$i] ?? null,
                    'type_of_ld'   => $table['type_of_ld'][$i] ?? null,
                    'conducted_by' => $table['conducted_sponsored_by'][$i] ?? null,
                ];

                if (collect((array) $rowData)->some(fn ($v) => trim((string) ($v ?? '')) !== '')) {
                    $tableRows->push($rowData);
                }
            }

            if ($tableRows->isNotEmpty()) {
                $tables->put($dynKey, $tableRows);
            }
        }

        return $tables;
    }

    private function mapEduRow(string $label, array $row): array
    {
        return [
            'level'           => $label,
            'school_name'     => $row['school_name'] ?? null,
            'degree_course'   => $row['basic_education'] ?? null,
            'from'            => $row['from'] ?? null,
            'to'              => $row['to'] ?? null,
            'highest_level'   => $row['highest_level'] ?? null,
            'year_graduated'  => $row['year_graduated'] ?? null,
            'academic_honors' => $row['scholarship_acadhonors'] ?? null,
        ];
    }

    private function dynKeys(array $data, string $pattern): array
    {
        $keys = array_keys(array_filter($data, function ($v, $k) use ($pattern) {
            return preg_match($pattern, $k);
        }, ARRAY_FILTER_USE_BOTH));
        sort($keys);
        return $keys;
    }
}
