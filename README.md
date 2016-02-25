# Form Builder DevTool
This package for the Woltlab Community Framwork aims to make form development easier. Out of the box, creating forms for the ACP/Backend in the WCF is cumbersome, verbose and requires a lot of boilerplate code.

This package is targetted at developers, not at forum owners/admins. It doesn't add any functionality on its own, it just provides tools to make development easier. You don't need to install this package, if you don't also use other packages that make use of this one.

_At this point, it only supports ACP forms, frontend form support will be a subject for the future._ If you find any bugs, have questions or suggestions for new features, feel free to start GitHub issues for them.

## Before
```php
<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\util\StringUtil;

class MyExampleAddForm extends AbstractForm {
    public $activeMenuItem = "wcf.acp.menu.link.example.add";

    public $exampleID = 0;
    public $title = '';
    public $description = '';

    public function readFormParameters() {
        parent::readFormParameters();

        if (isset($_POST['exampleID'])) $this->exampleID = intval($_POST['exampleID']);
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
            'exampleID' => $this->exampleID,
            'title' => $this->title,
            'description' => $this->description,
        ));
    }
}
```
As you can see, even this simple example contains a lot of boilerplate and repetition. We define a form that managed three attributes: `exampleID`, `title` and `description`, yet we need 62 lines of code to do that. We manually need to read the form parameters, define our save action, assign variables for template rendering and potentially even more.

## After
```php
<?php
namespace wcf\acp\form;
use wcf\form\FormBuilder;

class MyExampleAddForm extends FormBuilder {
    public $activeMenuItem = "wcf.acp.menu.link.example.add";

    protected function getAttributes() {
        return array(
            'title' => 'string',
            'description' => 'string',
        );
    }

    protected function getObjectActionType() {
        return 'wcf\data\example\ExampleAction';
    }
}
```
This short example accomplishes the same thing, yet it only requires us to write 18 lines of code. __That's a reduction of over 70%!__ I'd argue it's a lot more readable but undoubtedly it's not at all repetitive or unnecessarily verbose.

# How to use
Create a class that inherits from `wcf\form\FormBuilder`. FormBuilder contains two abstract methods that need to be implemented: `getAttributes()` and `getObjectActionType()`.

## getAttributes()
This is the main method you need to implement. You define which attributes your form has and can be filled out in the form. It will be used to determine which template variables to fill, which fields to use in your objectAction and how to validate your form attributes.
```php
protected function getAttributes() {
    return array(
        'description' => array(
            // The type to which the value should be converted to
            'type'     => 'string',
            // Should this value be required or not?
            'required' => true,
            // Skip this value when saving the object to the database
            'skip'     => false,
            // Validation rule to be applied to the value (see "Validation Rules")
            'rule'     => 'string',
        ),
    );
}
```
Alternatively, you can just specify the type of your attribute. This will automatically set it to a required attribute and will add an `isset` rule to it.
```php
protected function getAttributes() {
    return array(
        'description' => 'string',
    );
}
```

*The Form Builder automatically adds an ID field to your template variables called `primaryID`.*

### Validation Rules
- `isset` **(Default)** Verifies that the value is present in the request
- `integer` Verifies that the value is an integer
- `numeric` Verifies that the value is numeric, but doesn't have to be an integer
- `string` Verifies that the value is a string
- `digits:4` Verifies that the value contains exactly 3 digits (including decimal point, i.e. `2.34` but not `1.864`)
- `digitsBetween:4,8` Verifies that the value contains between 4 and 8 digits (inclusively)
- `email` Verifies that the value is a valid email address
- `url` Verifies that the value is a valid url
- `date` Verifies that the value is in a valid date format
- `class:className` Verifies that this is a valid ID for an object of the given class
- `custom:methodName` Add your own validation rule

#### Custom validation rules
You can write your validation rules if the provided ones do not suffice:
```php
protected function getAttributes() {
    return array(
        'categoryID' => array(
            'rule' => 'custom:validateCategoryID',
        ),
    );
}

private function validateCategoryID($value) {
    $category = new \wcf\data\category\Category($value);
    return $category->categoryID;
}
```
As you can see, by adding a custom method to your class (`validateCategoryID` in this case) you can write your own verification logic. Your method will automatically be called on validation and if it returns a [_falsy_](http://php.net/manual/en/language.types.boolean.php#language.types.boolean.casting) value, a `UserInputException` will be called with the type `custom`.

This is just a simple example and is equivalent to the rule `class:\wcf\data\category\Category`.

## getObjectActionType()
To save your model, you need to create a class that inherits from `wcf\data\AbstractDatabaseObjectAction`. The Form Builder uses your object action implementation to create and update your model. Therefore you need to specify the class and namespace for your action.
```php
protected function getObjectActionType() {
    return 'wcf\data\example\ExampleAction';

    // Or, with PHP >=5.5, if you're not planning to support lower versions:
    // return \wcf\data\example\ExampleAction::class;
}

```
## getObjectTypeName()
Needs to be set when `$requiresValidObject` is set to true. It should return a string containing the name of your model, so it can be instantiated for you on a request.
```php
protected function getObjectTypeName() {
    return 'wcf\data\example\Example';
}
```

## Settings
- `protected $modelAction` **(Default: `'create'`)** The action to perform on the object (e.g. `'create'` or `'update'`)
- `protected $usePersonalSave` **(Default: `false`)** If set to `true`, the Form Builder's implementation of `save()` will not be executed. You have to add your own implementation (including a `super::save()`-call)
- `protected $templateAction` **(Default: `'add'`)** The value that the template variable `action` will be set to
- `protected $requiresValidObject` **(Default: `false`)** Whether or not a request needs a valid object

## More complicated logic
In case your code requires more complicated logic that isn't included in the Form Builder yet, you can just overwrite these methods like so:
```php
public function validate()
{
    // This will also call FormBuilder's validation methods, 
    // so you don't need to do twice the amount of work, just your additional logic.
    super::validate();

    // Your complicated logic goes here
}
```

# Roadmap
There's no definitive roadmap yet, but I'm planning on adding the following features:

- [ ] An automated form template builder alongside the FormBuilder class (This will be the main priority for version 1.0)
- [x] Possibility for relations/dependent classes (i.e. each `Example` must be linked to a valid `ExampleCategory`); *This has been implemented with the "class" validation rule*
