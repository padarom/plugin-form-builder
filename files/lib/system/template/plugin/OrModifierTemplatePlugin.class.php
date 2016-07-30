<?php
namespace wcf\system\template\plugin;

use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

class OrModifierTemplatePlugin implements IModifierTemplatePlugin
{
    public function execute($arguments, TemplateEngine $tplObj)
    {
        if (count($arguments) < 2) {
            throw new SystemException("or modifier needs two arguments");
        }

        foreach ($arguments as $argument) {
            if ($argument || (gettype($argument) == 'string' && !strlen($argument))) {
                return $argument;
            }
        }

        return '';
    }
}