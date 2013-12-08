<?php 

namespace Doxedo\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doxedo\Core\Topic;
use Doxedo\Core\Build;
use Doxedo\Core\Project;
use RuntimeException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ProjectBuildCommand extends Command {

    protected function configure()
    {
        $this
            ->setName('project:build')
            ->setDescription('Build doxedo project')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename of the project to build'
            )
            ->addOption(
                'buildroot',
                null,
                InputOption::VALUE_REQUIRED,
                'Buildroot'
            )
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $buildroot = $input->getOption('buildroot');
        echo "Processing '" . $filename ."'\n";

        $build = new Build();
            
        $logger = new Logger('buildcommand');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        $build->setLogger($logger);
        
        $project = new Project();
        if ($buildroot!='') {
            if ($buildroot[0]!='/') {
                $buildroot = getcwd() . '/' . $buildroot;
            }
            $build->setBuildRoot($buildroot);
        }

        $project->loadXmlFile($filename);
        $build->setProject($project);
        $build->build();
        
        return;
    }
} 
