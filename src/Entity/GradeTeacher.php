<?php

namespace App\Entity;

use DH\DoctrineAuditBundle\Annotation\Auditable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @Auditable()
 */
class GradeTeacher {

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Teacher", inversedBy="grades")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var Teacher
     */
    private $teacher;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Grade", inversedBy="teachers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var Grade
     */
    private $grade;

    /**
     * @ORM\Column(type="grade_teacher_type")
     * @var GradeTeacherType
     */
    private $type;

    /**
     * @return Teacher
     */
    public function getTeacher(): Teacher {
        return $this->teacher;
    }

    /**
     * @param Teacher $teacher
     * @return GradeTeacher
     */
    public function setTeacher(Teacher $teacher): GradeTeacher {
        $this->teacher = $teacher;
        return $this;
    }

    /**
     * @return Grade
     */
    public function getGrade(): Grade {
        return $this->grade;
    }

    /**
     * @param Grade $grade
     * @return GradeTeacher
     */
    public function setGrade(Grade $grade): GradeTeacher {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return GradeTeacherType
     */
    public function getType(): GradeTeacherType {
        return $this->type;
    }

    /**
     * @param GradeTeacherType $type
     * @return GradeTeacher
     */
    public function setType(GradeTeacherType $type): GradeTeacher {
        $this->type = $type;
        return $this;
    }
}