<?php

namespace Oro\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\InventoryBundle\EventListener\CategoryInventoryDecrementFormViewListener;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Component\Testing\Unit\FormViewListenerTestCase;

class CategoryInventoryDecrementFormViewListenerTest extends FormViewListenerTestCase
{
    /**
     * @var RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStack;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var CategoryInventoryDecrementFormViewListener
     */
    protected $categoryFormViewListener;

    /**
     * @var BeforeListRenderEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrine;

    protected function setUp()
    {
        parent::setUp();
        $this->requestStack = new RequestStack();

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack->push($this->request);

        $this->doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryFormViewListener = new CategoryInventoryDecrementFormViewListener(
            $this->requestStack,
            $this->doctrine,
            $this->translator
        );

        $this->event = $this->getBeforeListRenderEventMock();
    }

    public function testOnCategoryEditIgnoredIfNoCategoryId()
    {
        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        $this->categoryFormViewListener->onCategoryEdit($this->event);
    }

    public function testOnCategoryEditIgnoredIfNoCategoryFound()
    {
        $this->em->expects($this->once())
            ->method('getReference');

        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($this->em);

        $this->request->expects($this->once())
            ->method('get')
            ->willReturn('1');

        $this->categoryFormViewListener->onCategoryEdit($this->event);
    }

    public function testCategoryEditRendersAndAddsSubBlock()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->willReturn('1');

        $this->em->expects($this->once())
            ->method('getReference')
            ->willReturn(new Category());

        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($this->em);

        $env = $this->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->once())
            ->method('getEnvironment')
            ->willReturn($env);

        $scrollData = $this->createMock(ScrollData::class);

        $this->event->expects($this->once())
            ->method('getScrollData')
            ->willReturn($scrollData);

        $env->expects($this->once())
            ->method('render');

        $scrollData->expects($this->once())
            ->method('addSubBlockData');

        $scrollData->expects($this->once())
            ->method('getData')
            ->willReturn(
                ['dataBlocks' => [1 => ['title' => 'oro.catalog.sections.default_options.trans']]]
            );

        $this->categoryFormViewListener->onCategoryEdit($this->event);
    }
}