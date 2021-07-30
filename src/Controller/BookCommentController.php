<?php

namespace App\Controller;

use App\Entity\BookComment;
use App\Entity\User;
use App\Form\BookCommentType;
use App\Repository\BookCommentRepositoryInterface;
use SchulIT\CommonBundle\Form\ConfirmType;
use SchulIT\CommonBundle\Helper\DateHelper;
use SchulIT\CommonBundle\Utils\RefererHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/book/comment")
 * @IsGranted("ROLE_BOOK_ENTRY_CREATOR")
 */
class BookCommentController extends AbstractController {

    private $repository;

    public function __construct(BookCommentRepositoryInterface $repository, RefererHelper $redirectHelper) {
        parent::__construct($redirectHelper);
        $this->repository = $repository;
    }

    /**
     * @Route("/add", name="add_book_comment")
     */
    public function add(Request $request, DateHelper $dateHelper) {
        /** @var User $user */
        $user = $this->getUser();

        $comment = (new BookComment())
            ->setDate($dateHelper->getToday());

        if($user->getTeacher() !== null) {
            $comment->setTeacher($user->getTeacher());
        }
        $form = $this->createForm(BookCommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'book.comment.add.success');
            $this->repository->persist($comment);
        }

        return $this->render('books/comment/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{uuid}/edit", name="edit_book_comment")
     */
    public function edit(BookComment $comment, Request $request) {
        $form = $this->createForm(BookCommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'book.comment.add.success');
            $this->repository->persist($comment);
        }

        return $this->render('books/comment/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{uuid}/remove", name="remove_book_student")
     */
    public function remove(BookComment $comment, Request $request, TranslatorInterface $translator) {
        $form = $this->createForm(ConfirmType::class, null, [
            'message' => 'book.comment.remove.confirm',
            'message_parameters' => [
                '%date%' => $comment->getDate()->format($translator->trans('date.format'))
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($comment);
            $this->addFlash('success', 'book.comment.remove.success');

            return $this->redirectToRoute('book');
        }

        return $this->render('books/comment/remove.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment
        ]);
    }
}