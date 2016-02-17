<?php 
namespace wcf\data\example;
use wcf\data\AbstractDatabaseObjectAction;

class RankAction extends AbstractDatabaseObjectAction
{
    protected $className = 'wcf\data\example\ExampleEditor';

    protected $permissionsDelete = ['admin.clan.example.canManageExamples'];

    protected $permissionsUpdate = ['admin.clan.example.canManageExamples'];
}