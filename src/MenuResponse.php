<?php

namespace PassManager;

/**
 *  DTO Ответ всех меню, возвращаемый в Manager
 */
class MenuResponse extends PropertyContainer
{
    private UserCommands $command;

    public function getCommand(): UserCommands
    {
        return $this->command;
    }


    public function setCommand(UserCommands $command): void
    {
        $this->command = $command;
    }

}