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
     * @param string $bin
     * @param bool $mimify
     */
    public function __construct($bin, $mimify)
    {
        $this->bin = $bin;
        $this->mimify = $mimify;
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
        $tmpfname = tempnam(sys_get_temp_dir(), 'mjmloutput');

        $builder = new ProcessBuilder();
        
        $builder->setPrefix($this->bin);
        $builder->setArguments([
                                   '-i',
                                   '-o' . $tmpfname,
                               ]);

        if ($this->mimify) {
            $builder->add('-m');
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
