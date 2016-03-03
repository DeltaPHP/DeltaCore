<?php

namespace DeltaCore;

use Webmozart\Console\Config\DefaultApplicationConfig;

class ConsoleConfig extends DefaultApplicationConfig
{
    /** @var  Application */
    protected $application;

    /**
     * ConsoleConfig constructor.
     * @param Application $application
     */
    public function __construct(Application $application, $name = null, $version = null)
    {
        $this->setApplication($application);
        parent::__construct($name, $version);
    }


    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('DeltaApp')
            ->setVersion('1.0.0')
            ->prepareCommands();
    }

    public function loadCommands()
    {
        $appConsole = ROOT_DIR . "/App/config/console.php";
        $projectConsole = ROOT_DIR . "/config/console.php";
        $appConsole = is_readable($appConsole) ? include $appConsole : [];
        $projectConsole = is_readable($projectConsole) ? include $projectConsole : [];

        $modulesConsole = $this->getApplication()->getModuleManager()->getListArrayConfigs("console");
        $console = array_merge($appConsole, $projectConsole, $modulesConsole);
        return $console;
    }

    protected function prepareCommands()
    {
        $commands = $this->loadCommands();
        foreach ($commands as $name => $params) {
            $command = $this->beginCommand($name);
            if (isset($params["description"])) {
                $command->setDescription($params["description"]);
            }
            $command->setHandler($params["handler"]);
            if (isset($params["arguments"])) {
                foreach ($params["arguments"] as $argument) {
                    call_user_func_array([$command, "addArgument"], $argument);
                }
            }
            if (isset($params["options"])) {
                $defaultOption = [
                    "shortName" => null, "flags" => 0, "description" => null, "default" => null, "valueName" => '...'
                ];
                foreach ($params["options"] as $name => $option) {
                    $option["longName"] = $name;
                    $option = array_merge($defaultOption, $option);
                    $this->addOption($option["longName"], $option["shortName"], $option["flags"], $option["description"], $option["default"], $option["valueName"]);
                }
            }
            $command->end();
        }
    }
}
