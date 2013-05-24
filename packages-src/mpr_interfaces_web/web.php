<?php

namespace mpr\interfaces;

interface web
{

    /**
     * @return array
     */
    public function getRoutings();

    /**
     * @return string
     */
    public function getTemplateDirectory();

}