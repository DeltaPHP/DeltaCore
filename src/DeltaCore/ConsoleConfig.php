<?php

namespace DeltaCore;

use DeltaUtils\ArrayUtils;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\SubCommandConfig;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Handler\Help\HelpHandler;

class ConsoleConfig extends DefaultApplicationConfig
{
    /** @var  Application */
    protected $application;

    protected function configure()
    {
        $this
            ->setIOFactory(array($this, 'createIO'))
            ->addEventListener(ConsoleEvents::PRE_RESOLVE, array($this, 'resolveHelpCommand'))
//            ->addEventListener(ConsoleEvents::PRE_HANDLE, array($this, 'printVersion'))
            ->addOption('help', null, Option::NO_VALUE, 'Display help about the command')
            ->addOption('quiet', 'q', Option::NO_VALUE, 'Do not output any message')
            ->addOption('verbose', 'v', Option::OPTIONAL_VALUE, 'Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug', null, 'level')
//            ->addOption('version', null, Option::NO_VALUE, 'Display this application version')
            ->addOption('ansi', null, Option::NO_VALUE, 'Force ANSI output')
            ->addOption('no-ansi', null, Option::NO_VALUE, 'Disable ANSI output')
            ->addOption('no-interaction', null, Option::NO_VALUE, 'Do not ask any interactive question')
            ->beginCommand('help')
            ->markDefault()
            ->setDescription('Display the manual of a command')
            ->addArgument('command', Argument::OPTIONAL, 'The command name')
            ->addOption('man', 'm', Option::NO_VALUE, 'Output the help as man page')
            ->addOption('ascii-doc', null, Option::NO_VALUE, 'Output the help as AsciiDoc document')
            ->addOption('text', 't', Option::NO_VALUE, 'Output the help as plain text')
            ->addOption('xml', 'x', Option::NO_VALUE, 'Output the help as XML')
            ->addOption('json', 'j', Option::NO_VALUE, 'Output the help as JSON')
            ->setHandler(function () {
                return new HelpHandler();
            })
            ->end();

        $this
            ->setName('DeltaApp')
//            ->setVersion('1.0.0')
            ->prepareCommands();
    }

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

    public function addOptionFromArray(CommandConfig $command, $name, array $params)
    {
        $default = [
            "shortName" => null, "flags" => 0, "description" => null, "default" => null, "valueName" => '...'
        ];
        $option = array_merge($default, $params);
        $option["longName"] = $name;
        $command->addOption($option["longName"], $option["shortName"], $option["flags"], $option["description"], $option["default"], $option["valueName"]);
    }

    public function addArgumentFromArray(CommandConfig $command, $name, array $params)
    {
        $default = [
            "flags" => 0, "description" => null, "default" => null,
        ];
        $option = array_merge($default, $params);
        $option["longName"] = $name;
        $command->addArgument($option["longName"], $option["flags"], $option["description"], $option["default"]);

    }

    public function setHandlerFromString($handler, CommandConfig $command)
    {
        if (is_string($handler)) {
            $application = $this->getApplication();
            $handler = function() use ($application, $handler) {
                return new $handler($application);
            };
        }
        $command->setHandler($handler);
    }

    public function setCommandParams(CommandConfig $command, array $params)
    {
        ArrayUtils::getAndCall($params, "description", [$command, "setDescription"]);
        ArrayUtils::getAndCall($params, "handler", [$this, "setHandlerFromString"], [$command]);
        if ($command instanceof SubCommandConfig) {
            ArrayUtils::getAndCall($params, "handlerMethod", [$command, "setHandlerMethod"]);
        }
        if (isset($params["options"])) {
            foreach ($params["options"] as $name => $optionParams) {
                $this->addOptionFromArray($command, $name, $optionParams);
            }
        }
        if (isset($params["arguments"])) {
            foreach ($params["arguments"] as $name => $argumentParams) {
                $this->addArgumentFromArray($command, $name, $argumentParams);
            }
        }
        if (isset($params["subCommands"])) {
            foreach ($params["subCommands"] as $subName => $subParams) {
                $subCommand = $command->beginSubCommand($subName);
                if (isset($subParams["default"]) && $subParams["default"]) {
                    $subCommand->markDefault();
                }
                $this->setCommandParams($subCommand, $subParams);
                $subCommand->end();
            }
        }
    }

    public function prepareCommands()
    {
        $commands = $this->loadCommands();
        foreach ($commands as $name => $params) {
            $command = $this->beginCommand($name);
            $this->setCommandParams($command, $params);
            $command->end();
        }
    }
}
