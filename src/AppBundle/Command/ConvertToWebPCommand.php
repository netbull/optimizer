<?php

namespace AppBundle\Command;

use WebPConvert\WebPConvert;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ConvertToWebPCommand
 * @package AppBundle\Command
 */
class ConvertToWebPCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('convert:webp')
            ->setDescription('Convert PNG/JPG into WebP format')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to the image directory.')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Converting images');

        $path = $input->getArgument('path');
        if (!$path) {
            $path = $this->getContainer()->getParameter('kernel.project_dir').'/tmp/';
        }

        // In case of a symlink
        $path = realpath($path);

        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->ignoreDotFiles(true)
            ->name('*.{jpg,jpeg,png,JPG,JPEG,PNG}')
        ;

        $progress = new ProgressBar($output);
        $progress->start(iterator_count($finder));
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $originalFile = $file->getRealPath();
            $outputFile = str_replace(".{$file->getExtension()}", '.webp', $originalFile);

            // Skip processing already processed images
            if (file_exists($outputFile)) {
                $progress->advance();
                continue;
            }

            $options = [];
            WebPConvert::convert($originalFile, $outputFile, $options);
            $progress->advance();
        }

        $progress->finish();

        $io->success('done');
    }
}
