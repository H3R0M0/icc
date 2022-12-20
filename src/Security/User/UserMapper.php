<?php

namespace App\Security\User;

use App\Entity\Student;
use App\Entity\User;
use App\Entity\UserType;
use App\Repository\StudentRepositoryInterface;
use App\Repository\TeacherRepositoryInterface;
use App\Utils\CollectionUtils;
use LightSaml\ClaimTypes;
use LightSaml\Model\Protocol\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use SchulIT\CommonBundle\Saml\ClaimTypes as SamlClaimTypes;
use SchulIT\CommonBundle\Security\User\AbstractUserMapper;

class UserMapper extends AbstractUserMapper {
    public const ROLES_ASSERTION_NAME = 'urn:roles';
    private LoggerInterface $logger;

    public function __construct(private array $typesMap, private TeacherRepositoryInterface $teacherRepository, private StudentRepositoryInterface $studentRepository, LoggerInterface $logger = null) {
        $this->logger = $logger ?? new NullLogger();
    }

    private function getUserType(string $type): UserType {
        if(!array_key_exists($type, $this->typesMap) || !in_array($type, UserType::cases())) {
            $this->logger
                ->notice(sprintf('User type "%s" is not a valid UserType. Setting type "user"', $type));
            return UserType::User;
        }

        return UserType::from($type);
    }

    /**
     * @param Response|array[] $data Either a SAMLResponse or an array (keys: SAML Attribute names, values: corresponding values)
     */
    public function mapUser(User $user, Response|array $data): User {
        if(is_array($data)) {
            return $this->mapUserFromArray($user, $data);
        } else if($data instanceof Response) {
            return $this->mapUserFromResponse($user, $data);
        }
    }

    private function mapUserFromResponse(User $user, Response $response): User {
        return $this->mapUserFromArray($user, $this->transformResponseToArray(
            $response,
            [
                ClaimTypes::COMMON_NAME,
                SamlClaimTypes::ID,
                ClaimTypes::GIVEN_NAME,
                ClaimTypes::SURNAME,
                ClaimTypes::EMAIL_ADDRESS,
                SamlClaimTypes::EXTERNAL_ID,
                SamlClaimTypes::TYPE,
            ],
            [
                self::ROLES_ASSERTION_NAME
            ]
        ));
    }

    /**
     * @param User $user User to populate data to
     * @param array<string, mixed> $data
     */
    private function mapUserFromArray(User $user, array $data): User {
        $username = $data[ClaimTypes::COMMON_NAME];
        $firstname = $data[ClaimTypes::GIVEN_NAME];
        $lastname = $data[ClaimTypes::SURNAME];
        $email = $data[ClaimTypes::EMAIL_ADDRESS];
        $roles = $data[self::ROLES_ASSERTION_NAME] ?? [ ];
        $type = $this->getUserType($data[SamlClaimTypes::TYPE]);

        if(!is_array($roles)) {
            $roles = [ $roles ];
        }

        if(count($roles) === 0) {
            $roles = [ 'ROLE_USER' ];
        }

        $user
            ->setUsername($username)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setUserType($type)
            ->setRoles($roles);

        if(UserType::Teacher === $type) {
            $externalId = $data[SamlClaimTypes::EXTERNAL_ID];

            if($externalId !== null) {
                $teacher = $this->teacherRepository->findOneByExternalId($externalId);

                if($teacher === null) {
                    $teacher = $this->teacherRepository->findOneByAcronym($externalId);
                }

                if ($teacher !== null) {
                    $user->setTeacher($teacher);
                } else {
                    $this->logger
                        ->notice(sprintf('Cannot map teacher with internal ID "%s" as such teacher does not exist.', $externalId));
                }
            } else {
                $this->logger
                    ->notice(sprintf('Cannot map teacher with username "%s" as his/her internal ID is not set.', $user->getUsername()));
            }
        } else if(UserType::Student === $type) {
            $studentId = $data[SamlClaimTypes::EXTERNAL_ID];

            if($studentId !== null) {
                $student = $this->studentRepository->findOneByExternalId($studentId);

                if ($student !== null) {
                    CollectionUtils::synchronize(
                        $user->getStudents(),
                        [$student],
                        fn(Student $student) => $student->getId()
                    );
                } else {
                    $this->logger
                        ->notice(sprintf('Cannot map student with student ID "%s" as such student does not exist.', $studentId));
                }
            } else {
                $this->logger
                    ->notice(sprintf('Cannot map student with username "%s" as his/her internal ID is not set.', $user->getUsername()));
            }
        } else if(UserType::Parent === $type) {
            $rawStudentIds = $data[SamlClaimTypes::EXTERNAL_ID];

            if($rawStudentIds !== null) {
                $studentIds = explode(',', $rawStudentIds);
                $students = $this->studentRepository->findAllByExternalId($studentIds);

                CollectionUtils::synchronize(
                    $user->getStudents(),
                    $students,
                    fn(Student $student) => $student->getId()
                );
            } else {
                $this->logger
                    ->notice(sprintf('Cannot map parent with username "%s" as his/her internal ID is not set.', $user->getUsername()));
            }
        }

        return $user;
    }
}