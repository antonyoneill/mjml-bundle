<?php

namespace NotFloran\MjmlBundle;

use Symfony\Component\Process\ProcessBuilder;

class Mjml
{
    /**
     * @var string
     */
    private $bin;

    /**
     * @var bool
     */
    private $mimify;

    /**
     * @var bool
     */
    private $useFile;

    /**
     * @param string $bin
     * @param bool $mimify
     */
    public function __construct($bin, $mimify, $useFile)
    {
        $this->bin = $bin;
        $this->mimify = $mimify;
        $this->useFile = $useFile;
    }

    /**
     * @param string $mjmlContent
     *
     * @throw \RuntimeException
     *
     * @return string
     */
    public function render($mjmlContent)
    {
        if ($this->useFile) {
            return $this->renderWithFile($mjmlContent);
        }

        return $this->renderWithPipes($mjmlContent);
    }

    /**
     * Use stdout to capture the output from the MJML node process
     *
     * @param $mjmlContent
     *
     * @return string
     */
    private function renderWithPipes($mjmlContent) {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->bin);
        $builder->setArguments([
            '-i',
            '-s',
            '-l',
            'strict',
        ]);

        if ($this->mimify) {
            $builder->add('--config.minify');
        }

        $builder->setInput($mjmlContent);

        $process = $builder->getProcess();
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:'."\n"
                .'stderr: "%s"'."\n"
                .'stdout: "%s"'."\n"
                .'command: %s.',
                $process->getStatus(),
                $process->getErrorOutput(),
                $process->getOutput(),
                $process->getCommandLine()
            ));
        }

        return $process->getOutput();
    }

    /**
     * Use a temporary file to capture the output from the MJML node process
     *
     * @param $mjmlContent
     *
     * @return string
     */
    public function renderWithFile($mjmlContent)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), 'mjmloutput');

        $builder = new ProcessBuilder();
        $builder->setPrefix($this->bin);
        $builder->setArguments([
                                   '-i',
                                   '-o' . $tmpfname,
        ]);

        if ($this->mimify) {
            $builder->add('--config.minify');
        }
        $builder->setInput($mjmlContent);

        $process = $builder->getProcess();
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'The exit status code \'%s\' says something went wrong:'."\n"
                .'stderr: "%s"'."\n"
                .'stdout: "%s"'."\n"
                .'command: %s.',
                $process->getStatus(),
                $process->getErrorOutput(),
                $process->getOutput(),
                $process->getCommandLine()
            ));
        }

        $renderedHtml = file_get_contents($tmpfname);

        unlink($tmpfname);

        return $renderedHtml;
    }
}
