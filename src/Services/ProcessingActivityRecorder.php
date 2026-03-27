<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Services;

use LumenSistemas\Lgpd\Contracts\HoldsPersonalData;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Exceptions\MissingDataSubjectException;
use LumenSistemas\Lgpd\Models\ProcessingActivity;

/**
 * Records processing activities with auto-filled classification data.
 */
class ProcessingActivityRecorder
{
    /**
     * Record a processing activity from a model that holds personal data.
     *
     * Auto-fills:
     * - `data_categories` from the classification keys
     * - `sensitivity` from the highest sensitivity level in the classification
     * - `data_subject_id` from the model's dataSubject relationship
     *
     * @param array<string, mixed> $attributes must include: activity, legal_basis, purpose, processed_at
     * @param null|list<string> $columns Subset of classification keys to include. When null, all classified columns are used.
     *
     * @throws MissingDataSubjectException if the model is not linked to a DataSubject
     */
    public function record(
        HoldsPersonalData $model,
        array $attributes,
        ?array $columns = null,
    ): ProcessingActivity {
        $classification = $model->dataClassification();

        if ($columns !== null) {
            $classification = array_intersect_key(
                $classification,
                array_flip($columns),
            );
        }

        $dataCategories = array_keys($classification);
        $sensitivity = $classification !== []
            ? DataSensitivity::highest(array_values($classification))
            : DataSensitivity::PUBLIC;

        $autoFill = [
            'data_categories' => $dataCategories,
            'sensitivity' => $sensitivity,
        ];

        $dataSubjectId = $model->dataSubject()->getParentKey();

        if ($dataSubjectId === null) {
            throw new MissingDataSubjectException();
        }

        $autoFill['data_subject_id'] = $dataSubjectId;

        $activity = new ProcessingActivity();
        $activity->fill([
            ...$autoFill,
            ...$attributes,
        ]);
        $activity->save();

        return $activity;
    }
}
