<?php
/**
 *  Form-Builder DevTool
 *  Copyright (C) 2016  Christopher Mühl
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace wcf\form;
use wcf\system\exception\UserInputException;
use wcf\system\exception\IllegalLinkException;
use wcf\util\StringUtil;
use wcf\system\WCF;

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

    protected $object = null;

    protected $valueList = array();

    protected $usePersonalSave = false;

    protected $templateName = 'formBuilder';

    /*
     * The action to be performed on the object action type.
     *
     * @var string
     */
    protected $modelAction = 'create';

    protected $templateAction = 'add';

    protected $requiresValidObject = false;

    /**
     * Return a list of attributes that is to be used in this form.
     *
     * @return array
     */
    protected abstract function getAttributes();

    /**
     * Namespace and class name for the object action type.
     *
     * @return string
     */
    protected abstract function getObjectActionType();

    /**
     * Namespace and class name for the model to be used in this form.
     *
     * @return string
     */
    protected function getObjectTypeName()
    {
        return false;
    }

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

    /**
     * Initializes the values list based on the defined attribute list.
     */
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

            // Custom rules need to be specified independently
            if ($options['rule'] == 'custom') {
                $ruleParts = explode(':', $options['rule']);
                $validationResult = user_call_func(array($this, $ruleParts[1]), $this->valueList[$name]);
                if (!$validatenResult) {
                    throw new UserInputException($name, $options['rule']);
                }
            }

            // Validate the attribute
            if (!Validator::validate($this->valueList[$name], $options['rule'])) {
                throw new UserInputException($name, $options['rule']);
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

        $attributeList = $this->buildAttributeList();

        // Exclude attributes that shouldn't be saved
        $data = $this->valueList;
        $matchedKeys = array_filter(array_keys($data), function($element) use ($attributeList) {
            if ($element == "primaryID")
                return false;

            return !$attributeList[$element]['skip'];
        });
        $data = array_intersect_key($data, array_flip($matchedKeys));

        // Include object when it is set and should be saved
        $objectArray = is_null($this->object) ? array() : array($this->object);

        // Create the object action
        $objectActionType = $this->getObjectActionType();
        $this->objectAction = new $objectActionType($objectArray, $this->modelAction, array(
            'data' => array_merge($this->additionalFields, $data),
        ));

        $this->objectAction->executeAction();
        $this->saved();

        // Rebuild the object
        $objectType = $this->getObjectTypeName();
        if ($objectType !== false) {
            $this->object = new $objectType($this->valueList["primaryID"]);
        }

        // Assign template variables
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
     * Reads the given parameters.
     *
     * @see \wcf\page\IPage::readParameters()
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->buildAttributeList();

        if ($this->requiresValidObject) {
            if (isset($_REQUEST['id'])) {
                $this->valueList["primaryID"] = intval($_REQUEST['id']);
            }

            $objectType = $this->getObjectTypeName();
            $this->object = new $objectType($this->valueList["primaryID"]);

            $tableIndexName = $objectType::getDatabaseTableIndexName();
            if (!$this->object->$tableIndexName) {
                throw new IllegalLinkException();
            }
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
        $isset = isset($haystack[$needle]);

        switch ($type) {
            case 'bool': // Checkbox should not be true or false, but 1 or 0
                return $isset ? 1 : 0;

            case 'int':
                return $isset ? intval($haystack[$needle]) : null;

            case 'string':
                return $isset ? StringUtil::trim($haystack[$needle]) : null;

            default:
                if (!$isset) {
                    return null;
                }
        }

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
                'action' => $this->templateAction,
                'object' => $this->object,
                'primaryID' => null,
            ),
            $this->valueList
        ));
    }
}
