<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\EnumField;
use App\Entity\GradeTeacher;
use App\Entity\GradeTeacherType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class GradeTeacherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GradeTeacher::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('GradeTeacher')
            ->setEntityLabelInPlural('GradeTeacher')
            ->setSearchFields(['type', 'id']);
    }

    public function configureFields(string $pageName): iterable
    {
        $type = EnumField::new('type')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', GradeTeacherType::class);
        $teacher = AssociationField::new('teacher');
        $grade = AssociationField::new('grade');
        $section = AssociationField::new('section');
        $id = IntegerField::new('id', 'ID')->hideOnForm();

        return [$id, $grade, $type, $teacher, $section];
    }
}
