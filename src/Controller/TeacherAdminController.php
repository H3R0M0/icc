<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Form\TeacherType;
use App\Repository\TeacherRepositoryInterface;
use App\Sorting\Sorter;
use App\Sorting\TeacherStrategy;
use App\Utils\RefererHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/teachers")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class TeacherAdminController extends AbstractController {

    private $sorter;
    private $repository;

    public function __construct(Sorter $sorter, TeacherRepositoryInterface $repository, RefererHelper $redirectHelper) {
        parent::__construct($redirectHelper);

        $this->sorter = $sorter;
        $this->repository = $repository;
    }

    /**
     * @Route("", name="admin_teachers")
     */
    public function index() {
        $teachers = $this->repository->findAll();
        $this->sorter->sort($teachers, TeacherStrategy::class);

        return $this->render('admin/teachers/index.html.twig', [
            'teachers' => $teachers
        ]);
    }

    /**
     * @Route("/add", name="add_teacher")
     */
    public function add(Request $request) {
        $teacher = new Teacher();
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->repository->persist($teacher);

            $this->addFlash('success', 'admin.teachers.add.success');
            return $this->redirectToRoute('admin_teachers');
        }

        return $this->render('admin/teachers/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit_teacher")
     */
    public function edit(Teacher $teacher, Request $request) {
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->repository->persist($teacher);

            $this->addFlash('success', 'admin.teachers.edit.success');
            return $this->redirectToRoute('admin_teachers');
        }

        return $this->render('admin/teachers/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/remove", name="remove_teacher")
     */
    public function remove(Teacher $teacher, Request $request) {

    }
}