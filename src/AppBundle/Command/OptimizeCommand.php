<?php

namespace AppBundle\Command;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Optimizing images');

        $path = $input->getArgument('path');
        if (!$path) {
            $path = $this->getContainer()->getParameter('kernel.project_dir').'/tmp/';
        }

        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->name('*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}')
        ;

        $optimizerChain = OptimizerChainFactory::create();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            var_dump($file->getRealPath());
            exit;
//            $originalFile = $file->getRealPath();
//            $optimisedFile = $file->getPath().'/'.$file->getBasename('.' . $file->getExtension()).'_optimized.'.$file->getExtension();

//            $optimizerChain->optimize($originalFile, $optimisedFile);
        }

        var_dump(iterator_count($finder));

        $io->success('done');
    }
}
