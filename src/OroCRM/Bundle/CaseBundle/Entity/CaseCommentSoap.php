<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Entity\SoapEntityInterface;

/**
 * @Soap\Alias("OroCRM.Bundle.CaseBundle.Entity.CaseComment")
 */
class CaseCommentSoap extends CaseComment implements SoapEntityInterface
{
    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $body;

    /**
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $public = true;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $case;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $contact;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $owner;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    /**
     * @param CaseComment $comment
     */
    public function soapInit($comment)
    {
        $this->id        = $comment->getId();
        $this->body      = $comment->getBody();
        $this->public    = $comment->isPublic();
        $this->case      = $this->getEntityId($comment->getCase());
        $this->contact   = $this->getEntityId($comment->getContact());
        $this->owner     = $this->getEntityId($comment->getOwner());
        $this->createdAt = $comment->getCreatedAt();
        $this->updatedAt = $comment->getUpdatedAt();
    }

    /**
     * @return bool|null
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * @param object $entity
     * @return integer|null
     */
    protected function getEntityId($entity)
    {
        if ($entity) {
            return $entity->getId();
        }

        return null;
    }
}
