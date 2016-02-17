<?php 
namespace wcf\acp\form;

class ExampleEditForm extends ExampleAddForm
{
    protected $modelAction = 'update';

    protected $templateAction = 'edit';

    protected $requiresValidObject = true;

    protected function getObjectTypeName() {
        return 'wcf\data\example\Example';
    }
}