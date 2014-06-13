<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller\Api\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\CaseBundle\Entity\CasePriority;
use OroCRM\Bundle\CaseBundle\Entity\CaseSource;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CommentControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    protected $commentCreateData = [
        'body'  => 'New comment',
        'owner' => 1,
    ];

    /**
     * @var int
     */
    protected static $caseId;

    /**
     * @var int
     */
    protected static $adminUserId = 1;

    /**
     * @var int
     */
    protected static $contactId;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();

        $this->loadFixtures(['OroCRM\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
    }

    protected function postFixtureLoad()
    {
        $case = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMCaseBundle:CaseEntity')
            ->findOneBySubject('Case #1');

        $contact = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneByEmail('daniel.case@example.com');

        $this->assertNotNull($case);
        $this->assertNotNull($contact);

        self::$caseId = $case->getId();
        self::$contactId = $contact->getId();
    }

    /**
     * @return integer
     */
    public function testCreate()
    {
        $result = $this->soapClient->createCaseComment(self::$caseId, $this->commentCreateData);
        $this->assertGreaterThan(0, $result, $this->soapClient->__getLastResponse());

        return $result;
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testCget($id)
    {
        $result = $this->soapClient->getCaseComments(self::$caseId);
        $result = $this->valueToArray($result);
        $comments = $result['item'];

        $this->assertCount(4, $comments);

        $this->assertArrayIntersectEquals(
            array(
                'id' => $id,
                'body' => $this->commentCreateData['body'],
                'public' => true,
                'case' => self::$caseId,
                'owner' => self::$adminUserId,
                'contact' => null,
                'updatedAt' => null,
            ),
            $comments[0]
        );

        $this->assertNotEmpty($comments[0]['createdAt']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     * @return array
     */
    public function testGet($id)
    {
        $result = $this->soapClient->getCaseComment($id);
        $comment = $this->valueToArray($result);

        $this->assertArrayIntersectEquals(
            array(
                'id' => $id,
                'body' => $this->commentCreateData['body'],
                'public' => true,
                'case' => self::$caseId,
                'owner' => self::$adminUserId,
                'contact' => null,
                'updatedAt' => null,
            ),
            $comment
        );

        $this->assertNotEmpty($comment['createdAt']);

        return $comment;
    }

    /**
     * @depends testGet
     * @param array $originalComment
     * @return integer
     */
    public function testUpdate(array $originalComment)
    {
        $id = $originalComment['id'];

        $updateData = [
            'body' => 'Updated comment',
            'public' => false,
            'contact' => self::$contactId
        ];

        $result = $this->soapClient->updateCaseComment($id, $updateData);
        $this->assertTrue($result, $this->soapClient->__getLastResponse());

        $updatedCase = $this->soapClient->getCaseComment($id);
        $updatedCase = $this->valueToArray($updatedCase);

        $this->assertNotEmpty($updatedCase['updatedAt']);

        $expectedCase = array_merge($originalComment, $updateData);
        $expectedCase['updatedAt'] = $updatedCase['updatedAt'];

        return $id;
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testDelete($id)
    {
        $result = $this->soapClient->deleteCaseComment($id);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $id . '" can not be found');
        $this->soapClient->getCaseComment($id);
    }
}
