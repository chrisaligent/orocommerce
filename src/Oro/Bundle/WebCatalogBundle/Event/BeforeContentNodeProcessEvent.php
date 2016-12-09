<?php

namespace Oro\Bundle\WebCatalogBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

class BeforeContentNodeProcessEvent extends Event
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var bool
     */
    protected $formProcessInterrupted = false;

    /**
     * @param FormInterface $form
     * @param mixed         $data
     */
    public function __construct(FormInterface $form, $data)
    {
        $this->form = $form;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return BeforeContentNodeProcessEvent
     */
    public function interruptFormProcess()
    {
        $this->formProcessInterrupted = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFormProcessInterrupted()
    {
        return $this->formProcessInterrupted;
    }
}