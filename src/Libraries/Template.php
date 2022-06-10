<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\TemplateNotFoundException;
use \BNETDocs\Libraries\Logger;
use \SplObjectStorage;

class Template
{
    public SplObjectStorage $opengraph;

    private $context;
    private string $template_directory = 'Templates';
    private string $template_extension = '.phtml';
    private string $template_file = '';

    public function __construct(&$context, string $template_file)
    {
        $this->opengraph = new SplObjectStorage();

        $this->setContext($context);
        $this->setTemplateFile($template_file);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getTemplateDirectory() : string
    {
        return $this->template_directory;
    }

    public function getTemplateExtension() : string
    {
        return $this->template_extension;
    }

    public function getTemplateFile() : string
    {
        return $this->template_file;
    }

    public function render() : void
    {
        try
        {
            $cwd = \getcwd();
            \chdir($cwd . \DIRECTORY_SEPARATOR . $this->template_directory);
            if (!\file_exists($this->template_file))
            {
                $e = new TemplateNotFoundException($this);
                throw $e;
            }
            require($this->template_file);
        }
        finally
        {
            \chdir($cwd); // always change back to last work directory
        }
    }

    public function setContext(&$context) : void
    {
        $this->context = $context;
    }

    public function setTemplateDirectory(string $template_directory) : void
    {
        $this->template_directory = $template_directory;
    }

    public function setTemplateExtension(string $template_extension) : void
    {
        $this->template_extension = $template_extension;
    }

    public function setTemplateFile(string $template_file) : void
    {
        $this->template_file = \sprintf('.%s%s%s',
            \DIRECTORY_SEPARATOR,
            \str_replace('/', \DIRECTORY_SEPARATOR, $template_file),
            $this->template_extension
        );
        Logger::logMetric('template', $this->template_file);
    }
}

