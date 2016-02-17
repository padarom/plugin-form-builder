# Form Builder DevTool
This package for the Woltlab Community Framwork aims to make form development easier. Out of the box, creating forms for the ACP/Backend in the WCF is cumbersome, verbose and requires a lot of boilerplate code.

This package is targetted at developers, not at forum owners/admins. It doesn't add any functionality on its own, it just provides tools to make development easier. You don't need to install this package, if you don't also use other packages that make use of this one.

## Before
```php
<?php
namespace wcf\acp\form;

use wcf\form\AbstractForm;

class MyExampleAddForm extends AbstractForm {
    public $exampleID = 0;
    public $title = '';
    public $description = '';

    public function readFormParameters() {
        parent::readFormParameters();

        if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
        if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
    }

    public function validate() {
        parent::validate();

        if (empty($this->title)) {
            throw new UserInputException('name');
        }

        if (empty($this->description)) {
            throw new UserInputException('description');
        }
    }

    public function save() {
        parent::save();

        $this->objectAction = new wcf\data\example\ExampleAction(array(), 'create', array(
            'data' => array(
                'exampleID' => $this->exampleID,
                'title' => $this->title,
                'description' => $this->description,
            ),
        ));

        $this->objectAction->executeAction();
        $this->saved();

        WCF::getTPL()->assign(array(
            'success' => true,
        ));
    }

    public function assignVariables() {
        parent::assignVariables();

        WCF::getTPL()->assign(array(
            'action' => 'add',
            'exampleID' => $this->branchID,
            'title' => $this->title,
            'description' => $this->description,
        ));
    }
}
```
As you can see, even this simple example contains a lot of boilerplate and repetition. We define a form that managed three attributes: `exampleID`, `title` and `description`, yet we need 59 lines of code to do that. We manually need to read the form parameters, define our save action, assign variables for template rendering and potentially even more.

## After
```php
<?php
namespace wcf\acp\form;

use wcf\form\FormBuilder;

class MyExampleAddForm extends FormBuilder {
    protected function getAttributes() {
        return array(
            'exampleID' => 'int',
            'title' => 'string',
            'description' => 'string',
        );
    }

    protected function getObjectActionType() {
        return 'wcf\data\example\ExampleAction';
    }
}
```
This short example accomplishes the same thing, yet it only requires us to write 18 lines of code. __That's a reduction of almost 70%!__ I'd argue it's a lot more readable but undoubtedly it's not at all repetitive or unnecessarily verbose.

# Roadmap
There's no definitive roadmap yet, but I'm planning on adding an automated form template builder alongside the FormBuilder class.
