<?php
namespace wcf\form;
use wcf\system\WCF;
use wcf\system\exception\UserInputException;

/**
 * Form Builder for easier form development
 *
 * @author      Christopher Mühl
 * @copyright   2016 Christopher Mühl
 * @package     io.padarom.devtools.formbuilder
 * @subpackage  form
 * @category    Community Framework
 */
abstract class FormBuilder extends AbstractForm {
    private $attributeList = null;

    private $valueList = array();

    protected $usePersonalSave = false;

    /*
     * The action to be performed on the object action type.
     *
     * @var string
     */
    protected $modelAction = 'create';

    /**
     * Return a list of attributes that is to be used in this form.
     *
     * @return array
     */
    protected abstract function getAttributes();

    /**
     * Namespace and class name for the object action type, if used.
     *
     * @return string
     */
    protected abstract function getObjectActionType();

    /**
     * Builds an attribute list from the values received in getAttributes()
     * and expands them if necessary.
     *
     * @return array
     */
    protected function buildAttributeList() 
    {
        if (!is_null($this->attributeList)) {
            return $this->attributeList;
        }

        $list = $this->getAttributes();
        foreach ($list as $name => &$options) {
            // Turn type hint to options array
            if (is_string($options)) {
                $options = array(
                    'type' => $options,
                );
            }

            // Add options if not specified
            $options = array_merge(array(
                'required' => true,
                'rule'     => 'isset',
                'skip'     => false,
            ), $options);
        }

        return $this->attributeList = $list;
    }

    protected function initializeValues()
    {
        foreach ($this->buildAttributeList() as $name => $options) {
            $this->valueList[$name] = null;
        }
    }

    /**
     * Is called when the form was submitted.
     *
     * @see \wcf\form\IForm::submit()
     */
    public function submit() 
    {
        parent::submit();
    }
    
    /**
     * Validates form inputs.
     *
     * @see \wcf\form\IForm::validate()
     */
    public function validate() 
    {
        parent::validate();

        $attributes = $this->buildAttributeList();
        foreach ($attributes as $name => $options) {
            // Don't verify if it isn't a required option
            if ($options['required'] == false)
                continue;

            // Validate the attribute
            if (!Validator::validate($this->valueList[$name], $options['rule'])) {
                throw new UserInputException($name);
            }
        }
    }

    /**
     * Saves the data of the form.
     *
     * @see \wcf\form\IForm::save()
     */
    public function save() 
    {
        parent::save();

        // Don't run any of this code if it's not desired.
        if ($this->usePersonalSave) {
            return;
        }

        $values = $this->valueList;
        $values = array_filter(array_flip($values), function($element) {
            return !$this->buildAttributeList[$element]['skip'];
        });

        $objectActionType = $this->getObjectActionType();
        $this->objectAction = new $objectActionType(array(), $this->modelAction, array(
            'data' => array_merge($this->additionalFields, $values),
        ));

        $this->objectAction->executeAction();
        $this->saved();

        WCF::getTPL()->assign(array(
            'success' => true,
        ));
    }

    /**
     * Reads the given form parameters.
     *
     * @see \wcf\form\IForm::readFormParameters()
     */
    public function readFormParameters() 
    {
        parent::readFormParameters();
 
        foreach ($this->buildAttributeList() as $name => $options) {
            $this->valueList[$name] = $this->readParameter($name, $_POST, $options['type']);
        }
    }

    /**
     * Reads a parameter from the given haystack and converts it to the given type.
     *
     * @param  mixed      $needle     The key of the desired value in the haystack array
     * @param  array      $haystack   The array to get the value from
     * @param  string     $type       The type the value should be converted to
     * @return mixed|null
     */
    protected function readParameter($needle, $haystack, $type = 'string') 
    {
        if (!isset($haystack[$needle]))
            return null;

        return $haystack[$needle];
    }

    /**
     * Calls the 'saved' event after the successful call of the save method.
     * This functions won't called automatically. You must do this manually, if you inherit AbstractForm.
     *
     * @see \wcf\form\AbstractForm::saved()
     */
    protected function saved() 
    {
        parent::saved();
    }

    /**
     * Reads/Gets the data to be displayed on this page.
     *
     * @see \wcf\page\IPage::readData()
     */
    public function readData() 
    {
        parent::readData();
    }

    /**
     * Assigns variables to the template engine.
     *
     * @see \wcf\page\IPage::assignVariables()
     */
    public function assignVariables() 
    {
        parent::assignVariables();

        $this->initializeValues();

        WCF::getTPL()->assign(array_merge(
            array(
                'action' => 'add'
            ),
            $this->valueList
        ));
    }
}
