<?php 
namespace wcf\acp\form;
use wcf\form\FormBuilder;

class ExampleAddForm extends FormBuilder
{
    public $activeMenuItem = "wcf.acp.menu.link.example.add";

    protected function getAttributes() {
        return array(
            'branchID' => array(
                'type' => 'int',
                'primary' => true,
                'skip' => true,
            ),
            'title' => 'string',
            'description' => 'string',
        );
    }

    protected function getObjectActionType() {
        return 'wcf\data\example\ExampleAction';
    }
}