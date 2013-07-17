<?php

namespace Environaut\Config;

interface IConfigHandler
{
    public function getConfig();
    public function addLocation($location);
    public function setLocations(array $locations);
    public function getLocations();
}
