<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 *
 * @author Aurélien Soulard <aurelien@vtech.fr>
 */
class DtoUniqueEntity extends Constraint
{
    public $message = 'This value is already used.';
    /**
     * @var string|null
     */
    public $em = null;
    /**
     * @var string
     */
    public $entityClass;
    /**
     * @var array
     */
    public $fields;
    /**
     * @var array
     */
    public $ids = ['id'];
    /**
     * @var string|null
     */
    public $errorPath = null;
    /**
     * @var bool
     */
    public $ignoreNull = true;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!is_array($this->fields)) {
            throw new UnexpectedTypeException($this->fields, 'array');
        }
        if (!is_array($this->ids)) {
            throw new UnexpectedTypeException($this->ids, 'array');
        }
        if (!is_string($this->entityClass)) {
            throw new UnexpectedTypeException($this->entityClass, 'string');
        }
        if ($this->em !== null && !is_string($this->em)) {
            throw new UnexpectedTypeException($this->em, 'string or null');
        }
        if ($this->errorPath !== null && !is_string($this->errorPath)) {
            throw new UnexpectedTypeException($this->errorPath, 'string or null');
        }
        if (count($this->fields) < 1) {
            throw new ConstraintDefinitionException('Please specify at least one field to check');
        }
        if (!is_bool($this->ignoreNull)) {
            throw new UnexpectedTypeException($this->ignoreNull, 'bool');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['entityClass', 'fields'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
