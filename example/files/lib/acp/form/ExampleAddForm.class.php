<?php 
namespace wcf\acp\form;
use wcf\form\FormBuilder;

class ExampleAddForm extends FormBuilder
{
    public $activeMenuItem = "wcf.acp.menu.link.example.add";

    protected function getAttributes() {
        return array(
            'title' => 'string',
            'description' => 'string',
            'isDisabled' => 'bool',
        );
    }

    protected function getObjectActionType() {
        return 'wcf\data\example\ExampleAction';
    }
}