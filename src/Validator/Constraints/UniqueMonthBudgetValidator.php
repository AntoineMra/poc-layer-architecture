<?php

namespace App\Validator\Constraints;

use App\Repository\BudgetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UnexpectedValueException;

final class UniqueMonthBudgetValidator extends ConstraintValidator
{
    public function __construct(
        private readonly BudgetRepository $budgetRepository,
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueMonthBudget) {
            throw new UnexpectedTypeException($constraint, UniqueMonthBudget::class);
        }

        if ($value === null) {
            throw new UnexpectedValueException('The date value should not be null for budgets');
        }

        if (!$value instanceof \DateTime) {
            throw new UnexpectedValueException($value, \DateTime::class);
        }

        $user = $this->userRepository->findOneBy([
            'username' => $this->security->getUser()->getUserIdentifier()
        ]);

        $budgets = $this->budgetRepository->findBy([
            'createdBy' => $user
        ]);

        foreach ($budgets as $budget) {
            if ($budget->getFormatedDate() === $value->format('m/y')) {
                $throwError = true;
            }
        }

        if ($throwError ?? false) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
