<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author Aurélien Soulard <aurelien@vtech.fr>
 */
class DtoUniqueEntityValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * DtoUniqueEntityValidator constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $dto
     * @param Constraint $constraint
     */
    public function validate($dto, Constraint $constraint)
    {
        if (!$constraint instanceof DtoUniqueEntity) {
            throw new UnexpectedTypeException($constraint, DtoUniqueEntity::class);
        }

        if (null === $dto) {
            return;
        }

        $entityClass = $constraint->entityClass;
        $fields = $this->normalizeFields($constraint->fields);

        if ($constraint->em) {
            $em = $this->registry->getManager($constraint->em);

            if (!$em) {
                throw new ConstraintDefinitionException(
                    sprintf('Object manager "%s" does not exist.', $constraint->em)
                );
            }
        } else {
            $em = $this->registry->getManagerForClass($entityClass);

            if (!$em) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'Unable to find the object manager associated with an entity of class "%s".',
                        $entityClass
                    )
                );
            }
        }

        /* @var $class \Doctrine\Common\Persistence\Mapping\ClassMetadata */
        $class = $em->getClassMetadata($entityClass);

        $criteria = [];
        $hasNullValue = false;

        foreach ($fields as $dtoFieldName => $entityFieldName) {
            if (!$class->hasField($entityFieldName) && !$class->hasAssociation($entityFieldName)) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'The field "%s" is not mapped by Doctrine for class %s, so it cannot be validated for uniqueness.',
                        $entityFieldName,
                        $entityClass
                    )
                );
            }

            $fieldValue = $this->getPropertyValue($dto, $dtoFieldName);
            if (null === $fieldValue) {
                $hasNullValue = true;
                break;
            }

            $criteria[$entityFieldName] = $fieldValue;

            if (null !== $criteria[$entityFieldName] && $class->hasAssociation($entityFieldName)) {
                /* Ensure the Proxy is initialized before using reflection to
                 * read its identifiers. This is necessary because the wrapped
                 * getter methods in the Proxy are being bypassed.
                 */
                $em->initializeObject($criteria[$entityFieldName]);
            }
        }

        if ($hasNullValue && $constraint->ignoreNull) {
            return;
        }

        if (empty($criteria)) {
            return;
        }

        $repository = $em->getRepository($entityClass);
        $result = $repository->findBy($criteria);

        if ($result instanceof \IteratorAggregate) {
            $result = $result->getIterator();
        }

        if ($result instanceof \Iterator) {
            $result->rewind();
        } elseif (is_array($result)) {
            reset($result);
        }

        if (0 === count($result)) {
            return;
        } elseif (1 === count($result)) {
            $entity = $result instanceof \Iterator ? $result->current() : current($result);
            if ($entity === $this->getEntityByIds($dto, $repository, $constraint->ids)) {
                return;
            }
        }

        reset($fields);
        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : key($fields);

        $this->context->buildViolation($constraint->message)
            ->atPath($errorPath)
            ->setCause($result)
            ->addViolation();
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getPropertyValue($object, $property)
    {
        try {
            $reflectionClass = new \ReflectionClass(get_class($object));
            $reflectionProperty = $reflectionClass->getProperty($property);

            if ($reflectionProperty->isStatic()) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'The field "%s" is a static property, so it cannot be validated for uniqueness.',
                        $property
                    )
                );
            }

            if (!$reflectionProperty->isPublic()) {
                $reflectionProperty->setAccessible(true);
            }

            return $reflectionProperty->getValue($object);
        } catch (\ReflectionException $e) {
            throw new ConstraintDefinitionException(
                sprintf(
                    'The field "%s" cannot be validated for uniqueness. (%s)',
                    $property,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param object $dto
     * @param ObjectRepository $repository
     * @param array $idProperties
     * @return object
     */
    private function getEntityByIds($dto, ObjectRepository $repository, array $idProperties)
    {
        $idProperties = $this->normalizeFields($idProperties);
        $ids = [];

        foreach ($idProperties as $dtoFieldName => $entityFieldName) {
            $fieldValue = $this->getPropertyValue($dto, $dtoFieldName);
            if (null === $fieldValue) {
                return null;
            }

            $ids[$entityFieldName] = $fieldValue;
        }

        return $repository->find($ids);
    }

    /**
     * @param array $initial
     * @return array
     */
    private function normalizeFields(array $initial)
    {
        $normalized = [];

        foreach ($initial as $dtoProperty => $entityProperty) {
            if (is_numeric($dtoProperty)) {
                $dtoProperty = $entityProperty;
            }

            $normalized[$dtoProperty] = $entityProperty;
        }

        return $normalized;
    }
}
