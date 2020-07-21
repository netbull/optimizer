<?php

namespace AppBundle\Command;

use App\ConsoleLogger;
use Exception;
use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\Loggers\EchoLogger;
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

        $path = $input->getArgument('path');
        if (!$path) {
            $path = $this->getContainer()->getParameter('kernel.project_dir').'/tmp/';
        }

        // In case of a symlink
        $path = realpath($path);

        $path .= '/img/p/8/4/2/0/';
        $finder = new Finder();

        $io->title('Converting images');
        $finder
            ->in($path)
            ->files()
            ->ignoreDotFiles(true)
//            ->name('*.{jpg,jpeg,png,JPG,JPEG,PNG}')
            ->name('8420-large_default.jpg')
        ;

        $progress = new ProgressBar($output);
        $progress->start(iterator_count($finder));
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $originalFile = $file->getRealPath();
            $outputFile = str_replace(".{$file->getExtension()}", '.webp', $originalFile);

            // Skip processing already processed images
//            if (file_exists($outputFile)) {
//                $progress->advance();
//                continue;
//            }

            $options = [
                'converters' => ['cwebp'],
//                'encoding' => 'lossless',
                'jpeg' => [
                    'default-quality' => 90,
                ],
            ];
            try {
                WebPConvert::convert($originalFile, $outputFile, $options, new ConsoleLogger($io));
            } catch (ConversionFailedException $e) {
                dump($e->getMessage());
                dump($originalFile);
            } catch (Exception $e) {
                dump($e->getMessage());
                dump($originalFile);
            }
            $progress->advance();
        }
        $progress->finish();

        $io->title('GC orphaned webp');
        $finder = new Finder();
        $finder
            ->in($path)
            ->files()
            ->ignoreDotFiles(true)
            ->name('*.webp')
        ;

        $progress = new ProgressBar($output);
        $progress->start(iterator_count($finder));
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $originalFile = $file->getRealPath();
            $jpg = str_replace(".{$file->getExtension()}", '.jpg', $originalFile);
            $jpeg = str_replace(".{$file->getExtension()}", '.jpeg', $originalFile);
            $png = str_replace(".{$file->getExtension()}", '.png', $originalFile);

            // Skip processing already processed images
            if (!file_exists($jpg) && !file_exists($jpeg) && !file_exists($png)) {
                unlink($originalFile);
            }
            $progress->advance();
        }
        $progress->finish();

        $io->success('done');
    }
}
