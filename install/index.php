<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

class mderrdx_start_module extends CModule
{
    //TODO: название директории
    public $MODULE_ID = 'mderrdx.start.module';

    public function __construct()
    {
        include(__DIR__ . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        //TODO: название модуля и описание
        $this->MODULE_NAME = 'Шаблоны модуля bitrix - CopyPaste';
        $this->MODULE_DESCRIPTION = 'Шаблон модуля для bitrix - CopyPaste';
    }

    /**
     * Зависимости от других модулей и их версий
     * Дополнить или удалить
     */
    private function getModuleDependens(): array 
    {
        return [
            'iblock' => '00.00.00',
        ];
    }

    /**
     * TODO: Заполнить этот массив, для добавления и удаления событий, что бы при удалении не оставался мусор в списке событий
     * Дополнить или удалить
     */
    private function moduleEvents(): array
    {
        return [
            [
                "module" =>"main",
                "event" => "OnUserTypeBuildList",
                "this_module" => $this->MODULE_ID,
                "class_name" => "ClassName", //TODO: Имя класса с namespace '\Mderrdx\Start\Module\ClassName'
                "method_name" =>  "ClassMethod" //TODO: имя метода
            ],
        ];
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if ($this->checkDependens()) {
            ModuleManager::registerModule($this->MODULE_ID);
        
        //$this->InstallDB();
        //$this->InstallEvents();
        //$this->InstallFiles();
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("MESSAGE_TITLE"), $this->GetPath() ."/install/step.php");
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        
        /*
        if ($request['step'] < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage('INSTALL_TITLE'), $this->GetPath() ."/install/unstep1.php");
        } elseif($request['step'] == 2) {
            //$this->UnInstallEvents();
            $this->UnInstallFiles();
            
            if ($request['savedate'] != 'Y') {
                $this->UnInstallDB();
            }

            
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('ACADEMY_D7_INSTALL_TITLE'),
                $this->GetPath() .'/install/unstep2.php'
            );
        }
        */
   
    }

    private function checkDependens(): bool
    {
        global $APPLICATION;
        foreach ($this->getModuleDependens() as $name => $version) {
            if (!ModuleManager::isModuleInstalled($name)) {
                $APPLICATION->throwException("Module: $name  not install");
                return false;
            }
            if (!CheckVersion(ModuleManager::getVersion($name), $version)) {
                $APPLICATION->throwException('Module: ' . $name . ' below version: '. $version);
                return false;
            }
        }

        return true;
    }

    public function InstallDB() : void
    {
        Loader::includeModule($this->MODULE_ID);
        $form = new \Mderrdx\Form\IBlock();
        $form->AddIBlockType();
        $id = $form->AddIblock();
        $form->AddProp($id);
    }

    public function UnInstallDB() : void
    {
        Loader::includeModule($this->MODULE_ID);
        $form = new \Mderrdx\Form\IBlock();
        $form->DelIblock();
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            $this->GetPath() . '/install/components',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components',
            true,
            true
        );
        return true;
    }

    public function UnInstallFiles() 
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/mderrdx/');
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        foreach($this->moduleEvents() as $event)
        {
            $eventManager->registerEventHandlerCompatible(
                $event["module"],
                $event["event"],
                $event["this_module"],
                $event["class_name"],
                $event["method_name"]
            );
        }
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        foreach($this->moduleEvents() as $event)
        {
            $eventManager->unRegisterEventHandler(
                $event["module"],
                $event["event"],
                $event["this_module"],
                $event["class_name"],
                $event["method_name"]
            );
        }
    }

    private function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }
}