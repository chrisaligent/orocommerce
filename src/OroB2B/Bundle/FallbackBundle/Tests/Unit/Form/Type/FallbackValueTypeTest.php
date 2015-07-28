<?php

namespace OroB2B\Bundle\FallbackBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Translation\TranslatorInterface;

use OroB2B\Bundle\FallbackBundle\Form\Type\FallbackValueType;
use OroB2B\Bundle\FallbackBundle\Form\Type\FallbackPropertyType;
use OroB2B\Bundle\FallbackBundle\Model\FallbackType;
use OroB2B\Bundle\FallbackBundle\Tests\Unit\Form\Type\Stub\TextTypeStub;

class FallbackValueTypeTest extends FormIntegrationTestCase
{
    /**
     * @var FallbackValueTypeTest
     */
    protected $formType;

    protected function setUp()
    {
        parent::setUp();

        $this->formType = new FallbackValueType();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface $translator */
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        return [
            new PreloadedExtension(
                [
                    FallbackPropertyType::NAME => new FallbackPropertyType($translator),
                    TextTypeStub::NAME => new TextTypeStub(),
                ],
                []
            )
        ];
    }

    /**
     * @param array $options
     * @param mixed $defaultData
     * @param mixed $viewData
     * @param mixed $submittedData
     * @param mixed $expectedData
     * @dataProvider submitDataProvider
     */
    public function testSubmit(array $options, $defaultData, $viewData, $submittedData, $expectedData)
    {
        $form = $this->factory->create($this->formType, $defaultData, $options);

        $formConfig = $form->getConfig();
        $this->assertNull($formConfig->getOption('data_class'));
        $this->assertEquals(FallbackPropertyType::NAME, $formConfig->getOption('fallback_type'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitDataProvider()
    {
        return [
            'percent with value' => [
                'options' => [
                    'type'    => 'percent',
                    'options' => ['type' => 'integer'],
                ],
                'defaultData'   => 25,
                'viewData'      => ['value' => 25, 'use_fallback' => false, 'fallback' => null],
                'submittedData' => ['value' => '55', 'use_fallback' => false, 'fallback' => ''],
                'expectedData'  => 55
            ],
            'text with fallback' => [
                'options' => [
                    'type'              => TextTypeStub::NAME,
                    'enabled_fallbacks' => [FallbackType::PARENT_LOCALE]
                ],
                'defaultData'   => new FallbackType(FallbackType::SYSTEM),
                'viewData'      => ['value' => null, 'use_fallback' => true, 'fallback' => FallbackType::SYSTEM],
                'submittedData' => ['value' => '', 'use_fallback' => true, 'fallback' => FallbackType::PARENT_LOCALE],
                'expectedData'  => new FallbackType(FallbackType::PARENT_LOCALE),
            ],
            'integer as null' => [
                'options' => [
                    'type' => 'integer',
                ],
                'defaultData'   => null,
                'viewData'      => ['value' => null, 'use_fallback' => false, 'fallback' => null],
                'submittedData' => null,
                'expectedData'  => null
            ],
        ];
    }

    public function testGetName()
    {
        $this->assertEquals(FallbackValueType::NAME, $this->formType->getName());
    }
}
