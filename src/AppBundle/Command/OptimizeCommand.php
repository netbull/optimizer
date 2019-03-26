<?php

namespace AppBundle\Command;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class OptimizeCommand
 * @package App\Command
 */
class OptimizeCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('optimize')
            ->setDescription('Optimize images')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to the image directory.')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Optimizing images');

        $path = $input->getArgument('path');
        if (!$path) {
            $path = $this->getContainer()->getParameter('kernel.project_dir').'/tmp/';
        }

        // In case of a symlink
        $path = realpath($path);
        $backupDir = $path.'/../../../../modules';

        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->ignoreDotFiles(true)
            ->name('*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}')
        ;

        $fileSystem = new Filesystem();
        $fileSystem->remove($backupDir);

        $optimizerChain = OptimizerChainFactory::create();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $backupFile = $backupDir.str_replace($path, '', $file->getRealPath());
            $fileSystem->copy($file->getRealPath(), $backupFile, true);

            $originalFile = $file->getRealPath();
            $optimizerChain->optimize($originalFile);
        }

        $io->success('done');
    }
}
