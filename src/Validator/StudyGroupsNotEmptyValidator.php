<?php

namespace App\Validator;

use App\Entity\UserType;
use App\Entity\UserTypeEntity;
use App\Utils\EnumArrayUtils;
use Countable;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StudyGroupsNotEmptyValidator extends ConstraintValidator {

    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor) {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint) {
        if(!$constraint instanceof StudyGroupsNotEmpty) {
            throw new UnexpectedTypeException($constraint, StudyGroupsNotEmpty::class);
        }

        if(!is_countable($value)) {
            throw new UnexpectedTypeException($value, Countable::class);
        }

        if(empty($constraint->propertyPath)) {
            throw new InvalidArgumentException('propertyPath must not empty.');
        }

        $userTypes = $this->propertyAccessor->getValue($this->context->getObject(), $constraint->propertyPath);

        if(!is_iterable($userTypes)) {
            throw new InvalidArgumentException('userTypes property is not iterable.');
        }

        foreach($userTypes as $userType) {
            if(!$userType instanceof UserTypeEntity) {
                throw new UnexpectedTypeException($userType, UserTypeEntity::class);
            }

            if($userType->getUserType()->equals(UserType::Student()) && count($value) === 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}