<?php

namespace App\View\Filter;

use App\Entity\User;
use App\Sorting\GradeNameStrategy;

class GradeFilter extends AbstractGradeFilter {

    public function handle(?string $gradeUuid, User $user, bool $setDefaultGrade = false) {
        $grades = $this->getGrades($user, $defaultGrade);

        if($setDefaultGrade === false) {
            $defaultGrade = null;
        }

        $grade = $gradeUuid !== null ?
            $grades[$gradeUuid] ?? $defaultGrade : $defaultGrade;

        $this->sorter->sort($grades, GradeNameStrategy::class);

        return new GradeFilterView($grades, $grade);
    }
}