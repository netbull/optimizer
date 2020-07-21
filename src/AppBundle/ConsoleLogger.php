<?php

namespace App;

use Symfony\Component\Console\Style\SymfonyStyle;
use WebPConvert\Loggers\BaseLogger;

/**
 * Class ConsoleLogger
 * @package App
 */
class ConsoleLogger extends BaseLogger
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * ConsoleLogger constructor.
     * @param SymfonyStyle $io
     */
    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * Handle log() by echoing the message.
     *
     * @param  string  $msg     message to log
     * @param  string  $style   style (null | bold | italic)
     * @return void
     */
    public function log($msg, $style = '')
    {
        $msg = htmlspecialchars($msg);
        if ($style == 'bold') {
//            echo '<b>' . $msg . '</b>';
            $this->io->writeln($msg);
        } elseif ($style == 'italic') {
//            echo '<i>' . $msg . '</i>';
            $this->io->writeln($msg);
        } else {
            $this->io->writeln($msg);
        }
    }

    /**
     * Handle ln by echoing a <br> tag.
     *
     * @return void
     */
    public function ln()
    {
        $this->io->writeln('');
    }
}
