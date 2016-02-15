<?php
namespace wcf\form;
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
    protected $validationRules = array();

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
     * Is called when the form was submitted.
     *
     * @see \wcf\form\IForm::submit()
     */
    public function submit() {
        parent::submit();
    }
    
    /**
     * Validates form inputs.
     *
     * @see \wcf\form\IForm::validate()
     */
    public function validate() {
        parent::validate();
    }

    /**
     * Saves the data of the form.
     *
     * @see \wcf\form\IForm::save()
     */
    public function save() {
        parent::save();
    }

    /**
     * Reads the given form parameters.
     *
     * @see \wcf\form\IForm::readFormParameters()
     */
    public function readFormParameters() {
        parent::readFormParameters();
    }

    /**
     * Calls the 'saved' event after the successful call of the save method.
     * This functions won't called automatically. You must do this manually, if you inherit AbstractForm.
     *
     * @see \wcf\form\AbstractForm::saved()
     */
    protected function saved() {
        parent::saved();
    }

    /**
     * Reads/Gets the data to be displayed on this page.
     *
     * @see \wcf\page\IPage::readData()
     */
    public function readData() {
        parent::readData();
    }

    /**
     * Assigns variables to the template engine.
     *
     * @see \wcf\page\IPage::assignVariables()
     */
    public function assignVariables() {
        parent::assignVariables();

        WCF::getTPL()->assign(array(

        ));
    }
}