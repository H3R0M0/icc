<?php

namespace App\Security\Voter;

use App\Entity\Document;
use App\Entity\GradeMembership;
use App\Entity\Student;
use App\Entity\User;
use App\Entity\UserType;
use App\Entity\UserTypeEntity;
use App\Repository\DocumentRepositoryInterface;
use App\Section\SectionResolverInterface;
use App\Utils\EnumArrayUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter {

    const New = 'new-document';
    const Edit = 'edit';
    const Remove = 'remove';
    const View = 'view';
    const ViewOthers = 'other-documents';
    const Admin = 'admin-documents';

    private $sectionResolver;
    private $documentRepository;
    private $accessDecisionManager;

    public function __construct(SectionResolverInterface $sectionResolver, AccessDecisionManagerInterface $accessDecisionManager, DocumentRepositoryInterface $documentRepository) {
        $this->sectionResolver = $sectionResolver;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->documentRepository = $documentRepository;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject) {
        $attributes = [
            static::Edit,
            static::Remove,
            static::View,
        ];

        return $attribute === static::New || $attribute === static::ViewOthers || $attribute === static::Admin ||
            ($subject instanceof Document && in_array($attribute, $attributes));
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch($attribute) {
            case static::New:
                return $this->canCreateDocument($token);

            case static::Edit:
                return $this->canEditDocument($subject, $token);

            case static::Remove:
                return $this->canRemoveDocument($token);

            case static::View:
                return $this->canViewDocument($subject, $token);

            case static::ViewOthers:
                return $this->canViewOtherDocuments($token);

            case static::Admin:
                return $this->canViewAdminOverview($token);
        }

        throw new \LogicException('This code should not be reached.');
    }

    private function canCreateDocument(TokenInterface $token) {
        return $this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN']);
    }

    private function canEditDocument(Document $document, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        foreach($document->getAuthors() as $author) {
            if($author->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    private function canRemoveDocument(TokenInterface $token) {
        return $this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN']);
    }

    private function canViewDocument(Document $document, TokenInterface $token) {
        if ($this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN']) || $this->accessDecisionManager->decide($token, ['ROLE_KIOSK'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();
        $section = $this->sectionResolver->getCurrentSection();

        if (EnumArrayUtils::inArray($user->getUserType(), [UserType::Student(), UserType::Parent(), UserType::Intern()]) !== true) {
            // Non students/parents/intern can view any student documents
            return true;
        }

        if ($section === null) {
            return false;
        }

        /** @var UserTypeEntity $visibility */
        foreach ($document->getVisibilities() as $visibility) {
            if ($user->getUserType()->equals($visibility->getUserType())) {
                if ($visibility->getUserType()->equals(UserType::Intern()) !== true) {
                    // Check grade memberships for students/parents
                    $studentIds = $user->getStudents()->map(function (Student $student) {
                        return $student->getId();
                    })->toArray();

                    foreach ($document->getGrades() as $documentStudyGroup) {
                        /** @var GradeMembership $membership */
                        foreach ($documentStudyGroup->getMemberships() as $membership) {
                            if ($membership->getSection()->getId() === $section->getId()) {
                                $studentId = $membership->getStudent()->getId();

                                if (in_array($studentId, $studentIds)) {
                                    return true;
                                }
                            }
                        }
                    }
                } else {
                    // Interns can view documents for Intern
                    return true;
                }
            }
        }

        return false;
    }

    private function canViewOtherDocuments(TokenInterface $token) {
        /** @var User $user */
        $user = $token->getUser();

        $isTeacher = $user->getUserType()->equals(UserType::Teacher());
        return $isTeacher || $this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN']);
    }

    private function canViewAdminOverview(TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_DOCUMENTS_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        return count($this->documentRepository->findAllByAuthor($user)) > 0;
    }
}